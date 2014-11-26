imagecompare
============

PHP Image Comparison Database Engine

Allows you to detect very similar or identical images of varying resolutions. Currently supports SQLite. Designed for command line applications.

For implementation details, please see my site at: http://www.gabrielhedges.com/project/image-comparison-search-engine/

##Installation

 * Copy files to library directory.
 * Add code below to your project.
```php
require_once("imagecompare.php");
require_once("image_db_pdo_sqlite.php");
```
##TODO

* OOP Design
* Prepared Statements
* Detection of flipped or rotated images.

##Examples

See the examples directory for a brief demo.

##Functions

###create_db($dbfile)

*$dbfile*: filename of the SQLite DB File

Initializes an SQLite database if one is not already created. You will want to call this before any other commands.

###image_exists($img_id)

*$img_id*: Image ID to check for in the database.

Returns true or false if the specified image ID already exists in the database. Useful for skipping images that have already been entered.

###image_comparison_array_create($filename)

*$filename*: Filename of the image.

Returns a comparison array, which you can later insert into the database using ```image_insert()```.

###image_insert($img_id, $compare_array)

*$img_id*: Unique ID you can use to later identify an image. A md5 hash of the filename isn't a bad idea.  
*$compare_array*: Image data created by ```image_comparison_array_create()```.  

Inserts image into the database.

###image_insert_multi($img_id, $compare_array, $queue_size)

*$img_id*: Unique ID you can use to later identify an image. A md5 hash of the filename isn't a bad idea.  
*$compare_array*: Image data created by ```image_comparison_array_create()```.  
*$queue_size*: Size of queue before flushed to database.

Queues up multiple SQL statements before flushing to the database for improved performance. You can call ```image_insert_multi(null, null, 0)``` to force a flush.

###image_get_similar($image_array, $percent)

*$image_array*: The image comparison array you're comparing against.
*$percent*: The percentage of difference allowed to before an image is no longer considered similar.

Returns an array of image IDs for images that are hopefully similar or the same.
