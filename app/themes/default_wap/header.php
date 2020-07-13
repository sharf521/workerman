<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <? if( $this->control=='index' && $this->func=='index') :  ?>
        <title>勿乱导购网-淘宝优惠券,京东优惠券,拼多多优惠券,手机赚钱,网赚,薅羊毛</title>
        <meta name="Keywords" content="勿乱导购网-淘宝优惠券,京东优惠券,拼多多优惠券,手机赚钱,网赚,薅羊毛" />
        <meta name="Description" content="致力于分享最新、最靠谱的淘宝、京东、拼多多优惠券和手机赚钱的方法，让用户能够省钱和兼职赚钱，随时随地，无需任何投资，动动手指就能赚取零花钱！" />
    <? else :?>
        <title><?php if($this->title!=''){echo $this->title.'-';}?><?=$this->site->name;?></title>
        <?
        if($this->keywords!=''){echo "<meta name='Keywords' content='{$this->keywords}' />\r\n";}
        if($this->description!=''){echo "<meta name='Description' content='{$this->description}' />\r\n";}
        ?>
    <? endif;?>
    <link rel="stylesheet" href="/themes/default_wap/default.css?1"/>
</head>
<body ontouchstart>
<header class="box wrap-head">
    <div class="header">
        <a class="header-logo" href="/">wuluan.com</a>
    </div>
    <nav class="box nav-bar">
        <a href="/coupon">淘宝优惠券</a>
        <a href="http://jd.wuluan.com">京东优惠券</a>
        <a href="/go/pdd">拼多多优惠券</a>
        <a href="/wz/" class="on">网赚/薅羊毛</a>
    </nav>
</header>