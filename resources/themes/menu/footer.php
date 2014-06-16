<div class="container">
    <?php if (is_active_sidebar('banner-footer')): ?>
        <div class="row">
            <?php dynamic_sidebar('banner-footer'); ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!Bunyad::options()->disable_footer): ?>
<div class="footer-section">
    <div class="container">
        <?php if (is_active_sidebar('main-footer')): ?>
            <div class="row">
                <?php dynamic_sidebar('main-footer'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php if (!Bunyad::options()->disable_lower_footer): ?>
<div class="copy-right">
    <div class="container">
        <?php if (is_active_sidebar('lower-footer')): ?>
            <?php dynamic_sidebar('lower-footer'); ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>





</div>
</div>

</section>
<?php wp_footer(); ?>
</body>
</html>