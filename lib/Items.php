<?php

namespace LikeLight;

class Items implements \Countable
{
    protected $cacheFile;
    protected $items;
    protected $controller;

    /**
     * @param Controller $controller
     * @param null $cachePath
     * @throws \Exception
     */
    public function __construct(Controller $controller, $cachePath = null)
    {
        $this->controller = $controller;

        if ($cachePath === null) {
            $this->cacheFile = dirname(__DIR__) . '/cache.' . $this->controller->getType() . '.json';
        } else {
            $this->cacheFile = $cachePath;
        }
        if (!is_writable(dirname($this->cacheFile))) {
            throw new \Exception("The cache directory is not writable");
        }

        if (file_exists($this->cacheFile)) {
            $this->items = json_decode(file_get_contents($this->cacheFile));
            $this->items->posts = (array)$this->items->posts;
        } else {
            $this->items = (object)['since' => null, 'posts' => null];
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return (isset($this->items->posts)
            ? count($this->items->posts)
            : 0
        );
    }

    /**
     * @return int
     */
    public function getLastCheck()
    {
        return (isset($this->items->since)
            ? $this->items->since
            : 0
        );
    }

    /**
     * @return $this
     */
    public function getInitialItems()
    {
        $edge = $this->controller->fb()->get(
            $this->controller->getEndpoint()
        )->getGraphEdge();
        if ($edge->count()) {
            $qs = [];
            $meta = $edge->getMetaData();
            parse_str(parse_url($meta['paging']['previous'], PHP_URL_QUERY), $qs);
            $this->items->since = $qs['since'];
            while ($edge !== null && $edge->count()) {
                foreach ($edge as $item) {
                    /* @var $item \Facebook\GraphNodes\GraphNode */
                    $this->items->posts[$item->getField('id')] = 0;
                    if ($this->controller->getMaxCount() > 0
                        && count($this->items->posts) == $this->controller->getMaxCount()
                    ) {
                        break 2;
                    }
                }
                $edge = $this->controller->fb()->next($edge);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function getNewItems()
    {
        $edge = $this->controller->fb()->get(
            $this->controller->getEndpoint($this->getLastCheck())
        )->getGraphEdge();
        if ($edge->count()) {
            $qs = [];
            $meta = $edge->getMetaData();
            parse_str(parse_url($meta['paging']['previous'], PHP_URL_QUERY), $qs);
            $this->items->since = $qs['since'];
            while ($edge !== null && $edge->count()) {
                $newItems = [];
                foreach ($edge as $item) {
                    /* @var $item \Facebook\GraphNodes\GraphNode */
                    $newItems[$item->getField('id')] = 0;
                }
                $this->items->posts = array_merge($newItems, $this->items->posts);
                $edge = $this->controller->fb()->previous($edge);
            }
            $this->items->posts = array_slice($this->items->posts, 0, $this->controller->getMaxCount(), true);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getNewLikeCounts()
    {
        $hasNewLikes = 0;
        if (!empty($this->items->posts)) {
            $batchRequests = [];
            foreach ($this->items->posts as $id => $count) {
                $batchRequests[$id] = $this->controller->fb()->request('GET', "/{$id}/likes");
            }
            foreach ($this->controller->fb()->sendBatchRequest($batchRequests) as $id => $response) {
                /* @var $response \Facebook\FacebookResponse */
                if (!$response->isError()) {
                    $edge = $response->getGraphEdge();
                    $newCount = $edge->count();
                    $hasNewLikes += ($newCount - $this->items->posts[$id]);
                    $this->items->posts[$id] = $newCount;
                }
            }
            file_put_contents($this->cacheFile, json_encode($this->items));
        }
        return $hasNewLikes;
    }

}