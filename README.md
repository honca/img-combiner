Simple PHP script for combine multiple images into one image. Useful for HTML coders..

Examples:

`php img-combiner.php -d .` - combines all png images into the one png image in the actual directory

`php img-combiner.php -d /home/honca -f "icon1.png,icon2.png" -m 10` - combines images icon1.png and icon2.png from my home directory into the one image with margin 10 pixels around each icon

`php img-combiner.php -d /home/honca/pictures -t "jpg,jpeg" -c 12 -o "jpg"` - combines all jpg images in pictures directory into the one jpg image with 12 items per line

`php img-combiner.php -d . -c 4 -m 1 -e positions.txt` - combines all png images into the one png image and exports all positions to the text file

Example of exported file:
number|left|top|width|height|imageName
1|1|1|32|32|32_bit.png
2|35|1|32|32|3d_glasses.png
3|69|1|32|32|64_bit.png
4|103|1|32|32|Plant.png
5|1|35|32|32|accept.png
6|35|35|32|32|accordion.png
7|69|35|32|32|account_balances.png
8|103|35|32|32|action_log.png
9|1|69|32|32|active_sessions.png