<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST'):
    $path = $_POST['path'] ?? null;
    $filename = $_POST['filename'] ?? null;
    $message = null;
endif;

if ($path && $filename):
    
    $name = $filename;

    $filename = parse_ini_file('config')['destination'];

    if ($filename):

        $filename .= $name.'.xml';
        $dirpath = $path . "/*.*";
        $files = array();
        $files = glob($dirpath);

        usort($files, function($x, $y) {
            return filemtime($x) > filemtime($y);
        });
        
        $date = date("d/m/Y H:i:s");

        $domtree = new DOMDocument('1.0', 'UTF-8');

        $xmlRoot = $domtree->createElement("Item");
        $xmlRoot = $domtree->appendChild($xmlRoot);
        
        $element = $domtree->createElement("ContentRating", " ");
        $element = $xmlRoot->appendChild($element);

        $element = $domtree->createElement("Added", $date);
        $element = $xmlRoot->appendChild($element);

        $element = $domtree->createElement("LockData", 'false');
        $element = $xmlRoot->appendChild($element);

        $element = $domtree->createElement("LocalTitle", $name);
        $element = $xmlRoot->appendChild($element);

        $element = $domtree->createElement("RunningTime", ' ');
        $element = $xmlRoot->appendChild($element);

        $element = $domtree->createElement("Genres", ' ');
        $element = $xmlRoot->appendChild($element);
        
        $element = $domtree->createElement("PlaylistItems");
        $element = $xmlRoot->appendChild($element);

        foreach ($files as $file):
            $playitem = $xmlRoot->appendChild($domtree->createElement("PlaylistItem"));
            $playitem->appendChild($domtree->createElement('Path', $file));
            $element->appendChild($playitem);
        endforeach;

        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        $domtree->save($filename);
        chmod($filename, 0777);
        $message = 'ok';
    else:
        $message = 'path';
    endif;
endif;

if (isset($filename) && !$filename && $message != 'path') $message = 'playlist';
if (isset($path) && !$path) $message = 'folder';

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" href="emby-icon.png"/>
    <link href="epc.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script type="text/javascript" src="epc.js"></script>
    <title>Emby Playlist Creator</title>
</head>
<body>
    <h1>Emby Playlist Creator</h1>
    <form method="post">
        <table>
            <tr>
                <td><label for="path">Folder path:</label></td>
                <td><input type="text" name="path" id="path" size="50" value="" /></td>
                <td><input type="file" name="file" id="file" /> Use for Copy Location only</td>
            </tr>
            <tr>
                <td><label for="filename">Playlist name:</label></td>
                <td><input type="text" size="50" name="filename" id="filename" value="" /></td>
                <td><input type="submit" value="Create"/></td>
            </tr>
        </table>
    </form><br>
<?php

if($message == 'ok'):
    echo "File $filename created successfully. Place it within Emby playlists folder to load.<br><br>";
    $xml = file_get_contents($filename);
    echo "<textarea>";
    print_r($xml);
    echo "</textarea>";
elseif($message == 'folder'):
    echo "<span class='error'>Missing folder path.</span>";
elseif($message == 'playlist'):
    echo "<span class='error'>Missing playlist name.</span>";
elseif($message == 'path'):
    echo "<span class='error'>Missing destination path in the 'config' file.</span>";
endif;

session_destroy();

?>
</body>
</html>
