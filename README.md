Simple PHP script for combine multiple images into one image. Useful for HTML coders..

Examples:

`php img-combiner.php -d .` - combines all png images into the one png image in the actual directory

`php img-combiner.php -d /home/honca -f "icon1.png,icon2.png" -m 10` - combines images icon1.png and icon2.png from my home directory into the one image with margin 10 pixels around each icon

`php img-combiner.php -d /home/honca/pictures -t "jpg,jpeg" -c 12 -o "jpg"` - combines all jpg images in pictures directory into the one jpg image with 12 items per line

`php img-combiner.php -d . -c 4 -m 1 -e positions.txt` - combines all png images into the one png image and exports all positions to the text file