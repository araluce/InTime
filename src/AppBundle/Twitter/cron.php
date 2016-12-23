<?php

require_once('TwitterAPIExchange.php');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$settings = array(
    'oauth_access_token' => "743191767164592129-ZQwH3OpEmZ7lji1Lo7xGNd9cJGzbI5x",
    'oauth_access_token_secret' => "Im8zcvNeTUQvCLuCp7PR9dPNE2GZCiISt7qLXReZ3Fd8Y",
    'consumer_key' => "wNhHfRrCJiKqtpGKNoESzZf10",
    'consumer_secret' => "UFrFsCIIMwRZK5KVGAfUBAb5VY53EuXmdTReirOGyi6uFJZ0vn"
);

$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
$requestMethod = "GET";
if (isset($_GET['user'])) {
    $user = $_GET['user'];
} else {
    $user = "iagdotme";
}
if (isset($_GET['count'])) {
    $count = $_GET['count'];
} else {
    $count = 20;
}
$getfield = "?screen_name=$user&count=$count";

$twitter = new \AppBundle\Twitter\TwitterAPIExchange($settings);
$string = json_decode($twitter->setGetfield($getfield)
                ->buildOauth($url, $requestMethod)
                ->performRequest(), $assoc = TRUE);

foreach ($string as $items) {
    echo "Time and Date of Tweet: " . $items['created_at'] . "<br />";
    echo "Tweet: " . $items['text'] . "<br />";
    echo "Tweeted by: " . $items['user']['name'] . "<br />";
    echo "Screen name: " . $items['user']['screen_name'] . "<br />";
    echo "Followers: " . $items['user']['followers_count'] . "<br />";
    echo "Friends: " . $items['user']['friends_count'] . "<br />";
    echo "Listed: " . $items['user']['listed_count'] . "<br /><hr />";
}

