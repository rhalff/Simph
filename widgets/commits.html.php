<?php
$feed = simplexml_load_file('https://github.com/rhalff/Simph/commits/master.atom');
?>
<div id="commits">
<h2><?=$feed->title?></h2>
<span class="updated"><?=$feed->updated?></span>
<?foreach($feed->entry as $entry):?>
 <div class="feed_entry">
    <h3><a href="<?=$entry->link->attributes()->href?>"><?=$entry->title?></a></h3>
    <span class="author">(<a href="<?=$entry->author->uri?>"><?=$entry->author->name?></a>)</span>
    <span class="updated"><?=$entry->updated?></span>
    <span class="gravatar"><img src="<?=$entry->children("http://search.yahoo.com/mrss/")->thumbnail->attributes()->url?>"/></span>
    <div class="content">
        <code>
        <?=$entry->content?>
        </code>
    </div>
 </div>
<?endforeach?>
</div>
