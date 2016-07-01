

@extends('bot-telegram::bot')

@section('content')
    <div class="row">
        <ol class="breadcrumb">
            <li><a href="#"><svg class="glyph stroked home"><use xlink:href="#stroked-home"></use></svg></a></li>
            <li class="active">Icons</li>
        </ol>
    </div><!--/.row-->

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Список уведомлений</h1>
        </div>
    </div><!--/.row-->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">Уведомления</div>
                <div class="panel-body">
                    <p class="toolbar">
                        <a class="create btn btn-default" href="javascript:">Добавить уведомление</a>
                    </p>
                    <p class="toolbar"><a href="https://core.telegram.org/bots/api#markdown-style" target="_blank">Справка по стилизации текста</a></p>
                    <div class="alert"></div>
                    <table id="table" data-toggle="table" data-url="{{URL::to('/bot-telegram/notifications')}}" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-pagination="true" data-sort-name="name" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-checkbox="true" >Ид</th>
                            <th data-field="name" data-sortable="true">Имя</th>
                            <th data-field="type" data-sortable="true">Тип</th>
                            <th data-field="message" data-sortable="false">Сообщение</th>
                            <th data-field="status" data-sortable="true">Статус</th>
                            <th data-field="photo" data-formatter="displayPhoto"  data-sortable="false">Картинка</th>
                            <th data-field="start" data-formatter="actionSubscribe" data-events="actionEvents" data-sortable="true">Рассылка</th>
                            <th data-field="action"
                                data-align="center"
                                data-formatter="actionFormatter"
                                data-events="actionEvents">Action</th>
                        </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>
    </div><!--/.row-->


    <div id="modal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="alert"></div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Имя</label>
                        <input type="text" class="form-control" name="name" placeholder="Имя">
                    </div>
                    <div class="form-group">
                        <label>Тип</label>
                        <input type="text" class="form-control" name="type" placeholder="Тип">
                    </div>
                    <div class="form-group">
                        <label>Сообщение</label>
                        <textarea type="text" class="form-control" name="message" placeholder="Ответное сообщение"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Активный</label>
                        <input type="checkbox" class="bs-checkbox" name="status">
                    </div>
                    <div class="withoutimg-div">
                        <label for="file">С фото:</label>
                        <input type="checkbox" name="withoutimg" id="withoutimg" value>
                    </div>
                    <div>
                        <label for="file">Файл</label>
                        <input type="file" multiple data-url="/bot-telegram/fileentry/add/" name="file" id="botfile">
                    </div>
                    <div class="files flex-rows">
                        <div class="thumbnail__placeholder"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary submit">Сохранить</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="modal-shcedule" class="modal fade">

    </div>

    <script>
        var API_URL = 'http://' + location.host + '/bot-telegram/notifications';
        var $table = $('#table').bootstrapTable({url: API_URL}),
                $modal = $('#modal').modal({show: false}),
                $alert = $('.alert').hide();
            window.currentNoti = {};
        var _current = {};
        $(function () {
            $modal.find('input[name="status"]').change(function(e) {
                ($(this).val() == "1") ? $(this).val("0") : $(this).val("1");
            });

            $(document).on("change", "input[name='withoutimg']", function() {
                var imgswitch = $modal.find("input[name='imgswitch[]']");
                if($(this).prop("checked")) {
                    $('.blackk').hide();
                }else{
                    $('.blackk').show();
                    imgswitch.prop("checked", false);
                }
            });

            $('.create').click(function () {
                showModal($(this).text());
            });
            $(document).on("click", ".submit", function () {
                var row = {}, imgs = $modal.find('input[type="radio"]:checked');
                $modal.find('input[name], input[type="checkbox"]').each(function () {
                    row[$(this).attr('name')] = $(this).val();
                });
                row.message =  CKEDITOR.instances['message'].getData();
                row.imgswitch = (imgs.length > 0) ? imgs.val() : 0;
                var url = ($modal.data('id')) ? API_URL+'/'+$modal.data('id') : API_URL+'';
                var type = $modal.data('id') ? 'PUT' : 'POST', message =  $modal.data('id') ? 'Обновление' : 'Создание';
                ajaxSend({
                    type:type,
                    data:row,
                    messages:{
                        bad:'Запись не обновилась! ',
                        good:'Запись обновилась ) '
                    },
                    url:url,
                    refresh:true,
                    afterLoad:function(data) {
                        uploadFiles(data, window.files, $("#botfile").data('url')+"{id}/notification", function(respond) {

                        })
                    },
                    success:function() {
                        $modal.modal('hide');
                        $table.bootstrapTable('refresh');
                        showAlert(this.messages.good, 'success');
                    }
                });
            });
        })
        function queryParams(params) {
            return {};
        }
        function actionFormatter(value) {
            return [
                '<a class="update" href="javascript:" title="Update Item"><i class="glyphicon glyphicon-edit"></i></a>',
                '<a class="remove" href="javascript:" title="Delete Item"><i class="glyphicon glyphicon-remove-circle"></i></a>',
            ].join('');
        }
        function displayPhoto(value) {
            return [
                    (value) ? '<img src="'+value+'" style="width:25%;min-height:100px;" />' : 'No image...'
            ];
        }
        function actionSubscribe(value, row) {
            var _class = (value == 1) ? "glyphicon-pause" : "glyphicon-play";
            var _text = (value == 1) ? "execution..." : "Start";
            return [
                '<a class="subscribe" href="javascript:" title="Запустить рассылку"><span class="texth">'+_text+'&nbsp&nbsp</span><i class="glyphicon '+_class+'"></i>&nbsp<span class="count'+row.id+'"></span></a>',
            ].join('');
        }
        // update and delete events
        window.actionEvents = {
            'click .update': function (e, value, row) {
                _current = row;
                showModal($(this).attr('title'), row);
            },
            'click .remove': function (e, value, row) {
                if (confirm('Вы уверены что хотите удалить уведомление?')) {

                    ajaxSend({
                        type:'delete',
                        messages:{ bad:'Ошибка удаления!', good:'Удаление прошло успешно!'},
                        url:API_URL + '/' + row.id,
                        success:function() {
                            $modal.modal('hide');
                            $table.bootstrapTable('refresh');
                            showAlert(this.messages.good, 'success');
                            if($(".files").find(".thumbnail").length < 1) {
                                $(".withoutimg-div").hide();
                            }
                        }
                    });
                }
            },
            'click .subscribe': function (e, value, row) {
                var _currentObj = $(e.currentTarget);
                if(row.start !== undefined) {
                    row.start = '1';
                    var _mess = (row.start == '0') ? 'По уведомлению <b>' + row.name + '</b> остановлена рассылка! )' : 'По уведомлению <b>' + row.name + '</b> запущена рассылка! )';
                    ajaxSend({
                        url:'/bot-telegram/sendNotifications',
                        type:'POST',
                        data:row,
                        messages:{
                            bad:"Ошибка рассылки...",
                            good:_mess
                        },
                        refresh:true,
                        success:function(data) {
                            //$table.bootstrapTable('refresh');
                            if (row.start == '1' && data.status == 'OK') {
                                _currentObj.find('.count'+row.id).text('0');
                                _currentObj.find('.texth').text('execution...');
                                _currentObj.find('i').addClass('glyphicon-pause')
                                .removeClass('glyphicon-play');
                            }
                            showAlert(data.text, (data.status == 'OK') ? 'success' : 'warning');
//                            $(_current).find('i').toggleClass(function() {
//                                if (row.start == '0') {
//                                    return 'glyphicon-play';
//                                } else {
//                                    return 'glyphicon-pause';
//                                }
//                            });
                        }
                    });
                }
            },
        };

        function showModal(title, row) {
            window.files = undefined;
            var cloneInput = $("#botfile");
            $("#botfile").replaceWith( cloneInput = cloneInput.clone( true ) );
            $(".withoutimg-div").hide();
            if(row && row.hasOwnProperty('id')) {
                showForm(row, title)();
                showFiles($(".files"), '/bot-telegram/fileentry/issetfiles/' + row.id + '/notification')
                        .then(function(data) {
                            if(data && data.length > 0) {
                                $(".withoutimg-div").show();
                                var imgswitch = $modal.find('#optradio' + row['imgswitch']);
                                if (imgswitch.length > 0) {
                                    imgswitch.prop('checked', true);
                                    $modal.find("input[name='withoutimg']").prop('checked', true);
                                } else {
                                    $modal.find("input[name='withoutimg']").prop('checked', false);
                                    $(".blackk").show();
                                }
                            }
                        });
            }else{
                showForm(row, title)();
            }

            $modal.modal('show');
        }

        function showForm(row, title) {
            return function() {
                row = row || {
                            id: '',
                            name: '',
                            message: '',
                            type: '',
                            status: '0',
                            imgswitch: ''
                        }; // default row value
                $modal.data('id', row.id);
                $modal.find('.modal-title').text(title);
                for (var name in row) {
                    $modal.find('input[name="' + name + '"]').val(row[name]);

                    if (name == 'message') {
                        CKEDITOR.instances['message'].setData(row[name]);
                    }

                    $modal.find('input[type="checkbox"]').filter('[name="' + name + '"]').prop('checked', (row[name] == "1") ? true : false);
                    //$modal.find('checkbox[name="' + name + '"]').val(row[name]);
                }
            }
        }

        $("#botfile").change(loadFile($(".files")));

    </script>
    <script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
    <script src="http://autobahn.s3.amazonaws.com/js/autobahn.min.js"></script>

    <script>
        $(function() {
            CKEDITOR.on( 'instanceReady', function( ev ) {
                ev.editor.dataProcessor.writer.setRules('br',
                        {
                            indent: false,
                            breakBeforeOpen: false,
                            breakAfterOpen: false,
                            breakBeforeClose: false,
                            breakAfterClose: false
                        });
            })
            CKEDITOR.replace('message', {
                customConfig: '/vendor/unisharp/laravel-ckeditor/config.js',
            });

            socketInit('sendNotifications', function(data) {
                var element = $(".subscribe .count"+data.id);
                if(data.status == 'last') {
                    element.siblings('i').addClass('glyphicon-play');
                    element.siblings('i').removeClass('glyphicon-pause');
                    element.siblings('.texth').text('Start');
                }

                element.text(data.count);
            });
        })
    </script>

@endsection