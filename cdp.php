<?php

//Set the headers to return an svg image
header('Content-type: image/svg+xml');

//check_CORS();

//Define constants
define("BOX_WIDTH", 300);
define("BOX_HEIGHT", 300);
define("CENTER_X", BOX_WIDTH / 2);
define("CENTER_Y", BOX_HEIGHT / 2);
define("SVG_TEMPLATE", '<?xml version="1.0" encoding="UTF-8"?>
<svg id="a" xmlns="http://www.w3.org/2000/svg" width="{{width}}" height="{{height}}">
    <defs>
        <style>
            text {
                font-family:Roboto-Regular,Roboto;
                font-size:{{tS}}px;
                fill : #{{tC}};
                font-weight:bold;
                alignment-baseline:middle;
            }
            .circle {
                fill:none;
                stroke: #{{sC}};
                stroke-width: {{sW}}px;
                visibility: {{visibility}};
            }
          </style>
    </defs>
    {{circles}}
</svg>
');

if ($_SERVER['REQUEST_METHOD'] == "GET") {

    //Get params or use default values
    $visibility = isset($_GET['hide']) ? "hidden" : "visible";
    $icon = isset($_GET['icon']) ? $_GET['icon'] : true;

    $textColor = isset($_GET['tc']) ? $_GET['tc'] : 'ffffff';
    $textSize = isset($_GET['ts']) ? $_GET['ts'] : 12;
    $textPadding = isset($_GET['tp']) ? $_GET['tp'] : 10;

    $strokeColor = isset($_GET['sc']) ? $_GET['sc'] : 'ffffff';
    $strokeWidth = isset($_GET['sw']) ? $_GET['sw'] : 2;

    $distanceFromCenter = isset($_GET['dc']) ? $_GET['dc'] : 80;

    $data = isset($_GET['data']) ? $_GET['data'] : 'python,javascript,php,nodejs';
    //Convert data to array
    $data = explode(',', $data);


    $total = count($data);

    $minCircleRadius = calculateMinCircleRadius($data, $textSize, $textPadding);
    $circlesPostitions = generateCirclesPositions($total, $distanceFromCenter);
    $circles = generateSvgCirclesAndText($data, $circlesPostitions, $minCircleRadius);

    //Echo the result svg
    echo generateSvgFromTemplate(array(
        "circles" => $circles,
        "visibility" => $visibility,
        "width" => BOX_WIDTH,
        "height" => BOX_HEIGHT,
        "tC" => $textColor,
        "tS" => $textSize,
        "sC" => $strokeColor,
        "sW" => $strokeWidth
    ));
}


/**
 * Generate the position of each circles
 */
function generateCirclesPositions($total, $distancefromCenter)
{
    $angle = 360 / $total;

    $positions = [];
    for ($i = 0; $i < $total; $i++) {
        //Calculate the angle
        $drawAngle = $angle * $i;
        //Calculate the position
        $x = CENTER_X + $distancefromCenter * cos(deg2rad($drawAngle));
        $y = CENTER_Y + $distancefromCenter * sin(deg2rad($drawAngle));
        array_push($positions, array($x, $y));
    }

    return $positions;
}


/**
 * Generate the circles and the text inside them
 */
function generateSvgCirclesAndText($data, $positions, $radius)
{
    $circles = '';
    for ($i = 0; $i < count($positions); $i++) {
        $circles .= '<circle class="circle" cx="' . $positions[$i][0] . '" cy="' . $positions[$i][1] . '" r="' . $radius . '"/>
        <text text-anchor="middle" x="' . $positions[$i][0] . '" y="' . $positions[$i][1] . '">' . $data[$i] . '</text>';
    }
    return $circles;
}

/**
 * Calculate the minimum circle radius by the text size and padding
 */
function calculateMinCircleRadius($data, $textSize, $textPadding)
{
    $maxWidth = 0;

    foreach ($data as $key => $text) {
        //Calculate the width of the text
        list($left,, $right) = imageftbbox($textSize, 0, "Roboto-Regular.ttf", $text);
        $width = $right - $left;
        //Save the max width
        if ($width > $maxWidth) {
            $maxWidth = $width;
        }
    }
    //Transform the max width to radius
    $maxWidth = intval($maxWidth / 2);
    return $maxWidth + $textPadding;
}

/**
 * Create a SVG from a template
 */
function generateSvgFromTemplate($values)
{
    $body = SVG_TEMPLATE;
    foreach ($values as $key => $value) {
        $body = str_replace("{{" . $key . "}}", $value, $body);
    }
    return $body;
}
