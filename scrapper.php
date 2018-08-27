<?php

require_once('WebScraping.class.php');

require_once("Db.php");

//initializing the  database object

$database = new Db;
$db = $database->getDbconnection();

//intializing the WeScraping object and retrieving the data from the html elements

$PostsData = new WebScraping('https://beingjaydesaicom.wordpress.com/2016/09/15/getting-started-with-github/'); 


//query will evaluate the expression of the node inside the html document

// item(0)->nodeValue will give one value from that node

// Getting the posts title

$posts_title = $PostsData->pathObj->query('//h1[@class="entry-title"]')->item(0)->nodeValue; 
// echo "string";
// exit;
// Getting the posts author

$posts_author = $PostsData->pathObj->query('//footer[@class="entry-footer"]/ul[@class="post-meta"]/li[@class="author vcard"]/a')->item(0)->nodeValue; 

// Getting the posts release date

$posts_Releasedate = $PostsData->pathObj->query('//footer[@class="entry-footer"]/ul[@class="post-meta"]/li[@class="posted-on"]/time[@class="entry-date published"]')->item(0)->nodeValue;

// getting names of all the recent posts

//note we havent used item(0)->nodeValue as we need all the data within <a> tag

$recent_posts = $PostsData->pathObj->query('//div[@class="widget-area"]/aside[@class="widget widget_recent_entries"]/ul/li/a');

if (!is_null($recent_posts)) {
$all_recent_posts = array();
foreach ($recent_posts as $post) {
$all_recent_posts[] = $post->nodeValue;
}
}

//getting all the tags of the post

//note we havent used item(0)->nodeValue as we need all the data within <a> tag

$tags = $PostsData->pathObj->query('//footer[@class="entry-footer"]/div[@class="meta-wrapper"]/ul[@class="post-tags"]/li/a');

//getting all the tags on the posts


if (!is_null($tags)) {
$posts_tags = array();
foreach ($tags as $tag) {
$posts_tags[] = $tag->nodeValue;
}
}

// using implode to function to get comma separated values of recent posts and tags before inserting it to db row,columns.

$inn_tags = implode(",", $posts_tags);
$ins_recent_posts = implode(",", $all_recent_posts);

//convert into date format Y-m-d to save format into mysql database

$entry_date =  date("Y-m-d" , strtotime($posts_Releasedate));

//insert query 

$insert_db_query = "INSERT INTO site_data SET 
title=:title,author=:author,  recent_posts= :recent_posts, tags= :tags,entry_date=:release";

//prepare the query

$exec = $db->prepare($insert_db_query);

//set the inputs and sanitize it properly

$title = htmlspecialchars(strip_tags($posts_title));
$release = htmlspecialchars(strip_tags($entry_date));
$author = htmlspecialchars(strip_tags($posts_author));
$al_recent_posts = htmlspecialchars(strip_tags($ins_recent_posts));
$al_tags = htmlspecialchars(strip_tags($inn_tags));

//bind parameters

$exec->bindParam(":title", $title);
$exec->bindParam(":release", $release);
$exec->bindParam(":author", $author);
$exec->bindParam(":recent_posts", $al_recent_posts);
$exec->bindParam(":tags", $al_tags);

if($exec->execute()){
echo "Data Inserted into db";
}
else{
echo "<pre>";
print_r($exec->errorInfo());
echo "</pre>";

}

?>