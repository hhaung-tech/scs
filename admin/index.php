<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/header.php';
require_once '../includes/sidebar-dynamic.php';
?>
<style>
.box-content.text-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
}
</style>
<div id="wrapper">
<!--    <div class="main-content">
        <div class="row small-spacing">
            <div class="col-lg-3 col-md-3 col-xs-12">
                <div class="box-content text-center">
                    <h4 class="box-title">Student Total Questions</h4>
                    <div class="c100 p100 small blue">
                        <span>20</span>
                        <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-xs-12">
                <div class="box-content text-center">
                    <h4 class="box-title">Alumni Total Questions</h4>
                    <div class="c100 p100 small blue">
                        <span>80</span>
                        <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-xs-12">
                <div class="box-content text-center">
                    <h4 class="box-title">Staff Total Questions</h4>
                    <div class="c100 p100 small blue">
                        <span>40</span>
                        <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-xs-12">
                <div class="box-content text-center">
                    <h4 class="box-title">Guardian Total Questions</h4>
                    <div class="c100 p100 small blue">
                        <span>40</span>
                        <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row small-spacing">
            <div class="col-lg-3 col-md-3 col-xs-12">
                <div class="box-content text-center">
                    <h4 class="box-title">Student Total Responses</h4>
                    <div class="c100 p100 small green">
                        <span>20</span>
                        <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-xs-12">
                <div class="box-content text-center">
                    <h4 class="box-title">Alumni Total Responses</h4>
                    <div class="c100 p100 small green">
                        <span>80</span>
                        <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-xs-12">
                <div class="box-content text-center">
                    <h4 class="box-title">Staff Total Responses</h4>
                    <div class="c100 p100 small green">
                        <span>40</span>
                        <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-xs-12">
                <div class="box-content text-center">
                    <h4 class="box-title">Guardian Total Responses</h4>
                    <div class="c100 p100 small green">
                        <span>40</span>
                        <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --!>
<?php 
require_once '../includes/footer.php';
?>
