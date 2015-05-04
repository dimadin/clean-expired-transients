Clean Expired Transients
===================

[Plugin homepage](http://blog.milandinic.com/wordpress/plugins/clean-expired-transients/) | [Plugin author](http://blog.milandinic.com/) | [Donate](http://blog.milandinic.com/donate/)

Safest and simplest transients garbage collector.

This plugin cleans every transient from database older than one minute using safe native WordPress function. It works on multisite too.

By default, it will check for expired transients once daily, though you can call it any time using `Clean_Expired_Transients::clean();`.

Clean Expired Transients is a very lightweight, it has no settings, just activate it and it works immediately.

Note that it can be used by developers in their project in any place, it doesn't requires activation and it's safe to use since it checks is there existing installation, just include it.