ActiveFrame
===========

A PHP Web Application Framework

To get started:

1. copy all files to a directory (let's say `active_frame`) inside your web server's root document directory.

2. Set `temp` directory (cache directory) permission to `777` if it's on *unix server

3. Access the directory from your web browser.  
e.g. `http://localhost/active_frame/`  
OR `http://localhost/active_frame/?path=welcome/`  
OR `http://localhost/active_frame/?path=welcome/index`  
Here `welcome` is the controller name & index is the accessible method of the controller.
You should be able to see the welcome page by now.

4. To connect with database, edit `config/db.config.php` with your own settings.

For more detail, please have a look at [Project Wiki](https://github.com/t83714/ActiveFrame/wiki).

