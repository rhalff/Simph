<div id="menu">
  <ul>
  <?foreach($this->pages as $url=>$page):?>
    <?=sprintf('<li><a href="%s" title="%s"%s>%s</a></li>',
           $page['path'],
           $page['description'],
           $page['path'] == $this->url ? ' class="selected"' : '',
           $page['title']
           ); ?>
    <?endforeach?>
  </ul>
</div>
