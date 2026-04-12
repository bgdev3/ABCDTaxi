                                        <!-- Navigation affiché pour un utilisateur authentifié -->
<?php include 'init_lang.php'; ?>

<div class="flex_header_user">

    <p class="main__content-title">
        <?php 
            echo $language->get('welcomeUser'); 
    
            if (isset($_SESSION['username'])) 
                echo $_SESSION['username']; 
        ?>
    </p>
    <a href="/public/user/logout/<?php echo trim($_SESSION['token']);?>" class="btnLogOut"><?php echo $language->get('logOut');?></a>
</div>