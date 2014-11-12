<?php

get_header();
thematic_abovecontent();
?>
<div id="main-content" class="news">
    <?php if(can_show_newsletter_form()): ?>
            <script type="text/javascript">
        //<![CDATA[
        if (typeof newsletter_check !== "function") {
        window.newsletter_check = function (f) {
            var re = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-]{1,})+\.)+([a-zA-Z0-9]{2,})+$/;
            if (!re.test(f.elements["ne"].value)) {
                alert("The email is not correct");
                return false;
            }
            if (f.elements["ny"] && !f.elements["ny"].checked) {
                alert("You must accept the privacy statement");
                return false;
            }
            return true;
        }
        }
        //]]>
        </script>
    <div id="what-new">
        <form class="row" method="post" action="<?php echo site_url()?>/wp-content/plugins/newsletter/do/subscribe.php" onsubmit="return newsletter_check(this)">
            <div class="col-sm-5 label-what-new" style="line-height: 35px">What New Is Signature Word</div>
            <div class="col-sm-5">
                <input  name="ne" size="30" required type="email" placeholder="enter your email address" class="site-input form-control" />
            </div>
            <div class="col-sm-2 no-padding">
                <input style="line-height: 35px" class="newsletter-submit btn-text no-padding" type="submit" value="Sign Up Now"/>
            </div>
        </form>
    </div>
    <?php endif?>
    <p class="discover_text">Click to discover what's new this month</p>
    <div class="row news-items">
        <div class="col-md-3  news-item month" id="news-item-month">
            <div id="news-item-month-content">
                <h2 style="font-size: 20px;color:#fff;text-align: center;padding-top: 15px;margin-top:0"><?php echo date('F') ?></h2>
            </div>
        </div>
        <?php 
            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
         ?>
        <div class="col-md-3  news-item" id="news-<?php the_ID(); ?>" >
            <?php echo get_the_post_thumbnail(get_the_ID(), 'full',['class'=>'img-responsive news-img']);   ?>
            <div class="sr-only">
                <h2><?php the_title()?></h2>
                <div class="news-content">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
        <?php
                endwhile;
            endif;
        ?>
    </div>
</div>
<div class="sr-only" id="news-detail-pattern">
    <div class="col-sm-12 news-detail relative" style="display:none">
        <hr class="divider" />
        <a class="toggle-news-detail" href="javascript:void(0)" style="color: #333;position: absolute;right:10px;cursor: pointer;top:25px;z-index: 9999;font-size: 18px">x</a>
        <div class="row">
            <div class="col-sm-5 col-xs-offset-1">
                <img src="" class="img-responsive news-detail-img" style="width: 100%" />
            </div>
            <div class="col-sm-5 news-detail-content">
                
            </div>
        </div>
    </div>
    <div class="clear" style="clear: both"></div>
</div>
<?php function show_news_item_script(){ ?> 
<script>
    $('.news-item').not('#news-item-month').on('click',function(){
        $('.news-items .news-detail').remove();
        $('.news-items .clear').remove();
        var total = $('.news-item').size();
        var index = $(this).index()+1;
        var nextIndex = 0;
        if($(window).width()<992){
            nextIndex = (index%2)+1;
        }
        if($(window).width()<768){
            nextIndex = index;
        }else if(index>(total-(total%4))){
             nextIndex = total;
        }else{
            var needIndex = (index%4)===0 ? 0 : 4-(index%4);
            nextIndex = index + needIndex;
        };
        //console.log(nextIndex);
        $('#news-detail-pattern .news-detail-img').attr('src',$(this).find('.news-img').attr('src'));
        $('#news-detail-pattern .news-detail-content').html($(this).find('.sr-only').html());
        var nextItem = $( ".news-item:eq( "+(nextIndex-1)+" )" );
        nextItem.after($('#news-detail-pattern').html());
        $('.news-items .news-detail').fadeIn();
        $('html,body').animate({
            scrollTop: $(".news-detail").offset().top
        });
    });
    
    $('body').on('click','.toggle-news-detail',function(){
        $(this).parent().fadeOut();
    });
</script>
<?php } ?>
<?php  add_action('thematic_after','show_news_item_script'); ?>
<?php
thematic_belowcontent();
get_footer();
?>
