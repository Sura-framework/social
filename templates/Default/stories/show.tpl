<script>
    var stori_num = 1;
    function move() {
        var element = document.getElementById("1sBar");
        var width = 1;
        var identity = setInterval(scene, 10);

        function scene() {
            if (width >= 100) {
                clearInterval(identity);
                if (element.style.width == 100+'%'){
                    addTimer('nextstori', function(){

                        //viiBox.clos('stories_box', 1);

                            $.post('/stories/show/{user_id}/'+stori_num+'/', function(d){
                                if (d == 'exit'){
                                    addTimer('hidestori', function(){
                                        viiBox.clos('stories_box', 1);
                                    }, 5000);
                                }else{
                                    $('#s_url').html(d);
                                    stori_num = stori_num+1;
                                    move();
                                }
                            });
                    }, 5000);
                    //removeTimer('nextstori');
                }
            } else {
                width++;
                element.style.width = width + '%';
                //element.innerHTML = width * 1  + '%';
            }
        }
    }
    function nextstori() {
        stori_num = stori_num+1;
        $.post('/stories/show/{user_id}/'+stori_num+'/', function(d){
            if (d == 'exit'){
                addTimer('hidestori', function(){
                    viiBox.clos('stories_box', 1);
                }, 10000);
            }else{
                $('#s_url').html(d);

                //move();
            }
        });
    }
    function prevstori() {
        stori_num = stori_num-1;
        if (stori_num<0) stori_num = 0;
        console.log(stori_num);
        $.post('/stories/show/{user_id}/'+stori_num+'/', function(d){
            if (d == 'exit'){
                addTimer('hidestori', function(){
                    viiBox.clos('stories_box', 1);
                }, 10000);
            }else{
                $('#s_url').html(d);
                //stori_num = stori_num+1;
                //move();
            }
        });
    }

</script>

<div class="miniature_box">
    <div class="miniature_pos" style="width:70%">
        <div class="load_photo_pad">
            <div class="err_red" style="display:none;font-weight:normal;"></div>
            <div class="d-flex justify-content-center" id="s_url">
                {progresss}
                <div class='row '>
                    <div class='col-1 m-auto'>
                        <div  class='text-center'>

                        </div>

                    </div>
                    <div class='col-10'>
                        <div class="p-2">
                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-eye" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.134 13.134 0 0 0 1.66 2.043C4.12 11.332 5.88 12.5 8 12.5c2.12 0 3.879-1.168 5.168-2.457A13.134 13.134 0 0 0 14.828 8a13.133 13.133 0 0 0-1.66-2.043C11.879 4.668 10.119 3.5 8 3.5c-2.12 0-3.879 1.168-5.168 2.457A13.133 13.133 0 0 0 1.172 8z"/>
                                <path fill-rule="evenodd" d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                            </svg> 0
                        </div>
                <img class="card-img-top" src="{s_url}" alt="">
            </div>
            <div class='col-1 m-auto'>
                <div class='text-center' onclick="nextstori()">
                    <svg width="3em" height="3em" viewBox="0 0 16 16" class="bi bi-arrow-right" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10.146 4.646a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L12.793 8l-2.647-2.646a.5.5 0 0 1 0-.708z"/>
                        <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5H13a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 8z"/>
                    </svg>
                </div >

            </div >

        </div>

            </div>
        </div>
        <div class="clear" style="margin-top:15px"></div>
        <div class="fl_r"><button class="btn btn-secondary" onClick="viiBox.clos('stories_box', 1)">Отмена</button></div>
        <div class="clear"></div>

    </div>
    <div class="clear" style="height:50px"></div>
</div>


