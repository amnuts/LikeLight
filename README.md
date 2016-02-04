# LikeLight

Light up an RGB led on the RaspberryPi whenever you get a Like on Facebook.  This uses PHP with the [PHPiWire](https://github.com/amnuts/phpiwire) as a requirement to interact with the GPIO pins.

[![Flattr this git repo](//button.flattr.com/flattr-badge-large.png)](https://flattr.com/submit/auto?fid=do3pln&url=https%3A%2F%2Fgithub.com%2Famnuts%2FLikeLight)

Right now this project is very undocumented and most untested.

### Very, very brief overview of a possible guide of what to do

First of all, you need to be doing this on a Raspberry Pi.  Make sure the Pi has PHP installed, you will also need to install a web server (apache or nginx, whatever floats your boat).

Next, install [Zephir](http://www.zephir-lang.com), clone the [PHPiWire](https://github.com/amnuts/phpiwire) repo and install the extension.

Once done, use composer to install the dependencies of this project.

You'll want to create an application on Facebook.  When you do that you'll have a secret key - that gets dropped into config.json (copy/rename config.example.json and update).  In there you'll also be able to define which pins the RGB led is hooked up to and the url of your authentication callback script.

Once done you'll need to log into Facebook via the scripts.  This means that the callback script needs to be accessible to the interwebs, so set up your router as appropriate.

Set up cron to call the index script every so often, five minutes or so, how ever often you'd like it to check for new likes.

# License

MIT: http://acollington.mit-license.org/