<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($sitemaps as $sitemap):?>
   <sitemap>
      <loc><?php echo $sitemap['loc'];?></loc>
      <lastmod><?php echo date('c', $sitemap['lastmod']);?></lastmod>
   </sitemap>
<?php endforeach;?>
</sitemapindex>