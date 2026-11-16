<footer class="site-footer">
    <div class="container footer-grid">
        <div class="f-brand">
            <a class="brand" href="<?php echo e(url('index.php')); ?>"><?php echo icon('pin', 24); ?> ConnectZW</a>
            <p>Connecting Zimbabwean businesses with local customers. Grow local, build Zimbabwe.</p>
            <div class="social">
                <a href="#" aria-label="Facebook"><?php echo icon('facebook', 18); ?></a>
                <a href="#" aria-label="Instagram"><?php echo icon('instagram', 18); ?></a>
                <a href="#" aria-label="Twitter"><?php echo icon('twitter', 18); ?></a>
                <a href="#" aria-label="LinkedIn"><?php echo icon('linkedin', 18); ?></a>
            </div>
        </div>
        <div>
            <h4>Quick Links</h4>
            <a href="<?php echo e(url('index.php')); ?>">Home</a>
            <a href="<?php echo e(url('about.php')); ?>">About Us</a>
            <a href="<?php echo e(url('contact.php')); ?>">Contact Us</a>
            <a href="<?php echo e(url('blog.php')); ?>">Blog</a>
            <a href="<?php echo e(url('faq.php')); ?>">FAQs</a>
        </div>
        <div>
            <h4>For Businesses</h4>
            <a href="<?php echo e(url('register.php')); ?>">List Your Business</a>
            <a href="<?php echo e(url('dashboard.php')); ?>">Business Dashboard</a>
            <a href="<?php echo e(url('blog.php')); ?>">Resources</a>
            <a href="<?php echo e(url('pricing.php')); ?>">Pricing</a>
        </div>
        <div>
            <h4>Support</h4>
            <a href="<?php echo e(url('faq.php')); ?>">Help Center</a>
            <a href="<?php echo e(url('terms.php')); ?>">Terms of Use</a>
            <a href="<?php echo e(url('privacy.php')); ?>">Privacy Policy</a>
            <a href="<?php echo e(url('contact.php')); ?>">Contact Support</a>
        </div>
        <div class="f-news">
            <h4>Subscribe to our newsletter</h4>
            <p>Get the latest updates and tips for your business.</p>
            <form method="POST" action="<?php echo e(url('subscribe.php')); ?>" class="news-form">
                <?php echo csrf_field(); ?>
                <input type="email" name="email" placeholder="Enter your email" required aria-label="Email address">
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© <?php echo date('Y'); ?> ConnectZW. All rights reserved.</span>
        <span class="proud">Proudly Zimbabwean <?php echo flag_zw(20); ?></span>
    </div>
</footer>
<script src="<?php echo e(url('assets/js/main.js')); ?>?v=3"></script>
</body>
</html>
