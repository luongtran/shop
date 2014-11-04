<?php

get_header();
thematic_abovecontent();
?>
<div id="main-content">
    <?php 
        thematic_above_indexloop();
        thematic_indexloop();
        thematic_below_indexloop();
    ?>
</div>
<?php
thematic_belowcontent();
get_footer();
?>
