

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
            <h1 class="page-header">Commands list</h1>
        </div>
    </div><!--/.row-->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">Команды бота</div>
                <div class="panel-body">
                    <p class="toolbar">
                        <a class="create btn btn-default" href="javascript:">Добавить комманду</a>
                    </p>
                    <div class="alert"></div>
                    <table id="table" data-toggle="table" data-url="{{URL::to('/bot-telegram/commands')}}" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-pagination="true" data-sort-name="name" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-checkbox="true" >Ид</th>
                            <th data-field="name" data-sortable="true">Имя</th>
                            <th data-field="type" data-sortable="true">Тип</th>
                            <th data-field="message" data-sortable="false">Сообщение</th>
                            <th data-field="status" data-sortable="true">Статус</th>
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
                    {{--{!! $tags !!}--}}
                    <div class="form-group">
                        <label>Ключевые слова</label>
                        <select name="tags" class="tags-input" multiple id="">
                            @if($tags)
                                @foreach($tags as $tag)
                                    <option value="{{$tag->id}}">{{$tag->name}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Тип</label>
                        <input type="text" class="form-control" name="type" placeholder="Тип">
                    </div>
                    <div class="form-group">
                        <label>Ответное сообщение</label>
                        <textarea type="text" class="form-control" name="message" placeholder="Ответное сообщение"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Статус</label>
                        <input type="checkbox" class="bs-checkbox" name="status">
                    </div>
                    {{--<button class="btn btn-primary addsubcommand g">Добавить подкоманду</button>--}}
                    <div id="list_commands">
                        {{--<h4 class="modal-title"><b>Список подкомманд:</b></h4>--}}
                        {{--<hr>--}}
                        {{--<div class="form-group">--}}
                            {{--<label>Ключ подкоманды</label>--}}
                            {{--<input type="text" class="form-control" name="type" placeholder="Тип">--}}
                        {{--</div>--}}
                        {{--<div class="form-group">--}}
                            {{--<label>Ответное сообщение</label>--}}
                            {{--<textarea type="text" class="form-control" name="message" placeholder="Ответное сообщение"></textarea>--}}
                        {{--</div>--}}
                        {{--<div class="form-group">--}}
                            {{--<label>Системное</label>--}}
                            {{--<input type="checkbox" class="bs-checkbox" name="system">--}}
                        {{--</div>--}}
                        {{--<hr>--}}
                        {{--<div class="form-group">--}}
                            {{--<label>Ключ подкоманды</label>--}}
                            {{--<input type="text" class="form-control" name="type" placeholder="Тип">--}}
                        {{--</div>--}}
                        {{--<div class="form-group">--}}
                            {{--<label>Ответное сообщение</label>--}}
                            {{--<textarea type="text" class="form-control" name="message" placeholder="Ответное сообщение"></textarea>--}}
                        {{--</div>--}}
                        {{--<div class="form-group">--}}
                            {{--<label>Системное</label>--}}
                            {{--<input type="checkbox" class="bs-checkbox" name="system">--}}
                        {{--</div>--}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary submit">Сохранить</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' } });
        var API_URL = window.location.protocol+'//' + location.host + '/bot-telegram/commands';
        var $table = $('#table').bootstrapTable({url: API_URL}),
                $modal = $('#modal').modal({show: false}),
                $alert = $('.alert').hide();
        $(function () {
            // create event
            $modal.find('input[type="checkbox"]').change(function(e) {
                ($(this).val() == "1") ? $(this).val("0") : $(this).val("1");
            });

            /*
            * Создание новых данных
            * */
            $('.create').click(function () {
                showModal($(this).text());
            });

            $('.addsubcommand').click(function(e) {
                var id = $modal.data('id');
                console.log(id);
                var list = new ListCm('#list_commands', id);
                //list.load();
            });


            $modal.find('.submit').click(function () {
                var row = {};
                $modal.find('input[name], textarea[name], input[type="checkbox"]').each(function () {
                    row[$(this).attr('name')] = $(this).val();
                });
                row.message =  CKEDITOR.instances['message'].getData();
                var multi = $modal.find('select[name="tags"]').val() || [];
                row.tags_list = multi.join(',');

                var p = ($modal.data('id')) ? '/'+$modal.data('id') : '';
                var url = ($modal.data('id')) ? API_URL+'/'+$modal.data('id') : API_URL+'';
                var type = $modal.data('id') ? 'PUT' : 'POST';

                ajaxSend({
                    type:type,
                    data:row,
                    messages:{
                        bad:'Запись не обновилась! ',
                        good:'Запись обновилась ) '
                    },
                    url:url,
                    refresh:true,
                    success:function() {
                        $modal.modal('hide');
                        $table.bootstrapTable('refresh');
                        showAlert(this.messages.good, 'success');
                    }
                });

            });
        });
        function queryParams(params) {
            return {};
        }
        function actionFormatter(value) {
            return [
                '<a class="update" href="javascript:" title="Update Item"><i class="glyphicon glyphicon-edit"></i></a>',
                '<a class="remove" href="javascript:" title="Delete Item"><i class="glyphicon glyphicon-remove-circle"></i></a>',
            ].join('');
        }
        // update and delete events
        window.actionEvents = {
            'click .update': function (e, value, row) {
                showModal($(this).attr('title'), row);
            },
            'click .remove': function (e, value, row) {
                if (confirm('Вы уверены что хотите удалить комманду?')) {
                    console.log(row);
                    ajaxSend({
                        type:'delete',
                        messages:{ bad:'Ошибка удаления!', good:'Удаление прошло успешно!'},
                        url:API_URL + '/' + row.id,
                        success:function() {
                            $modal.modal('hide');
                            $table.bootstrapTable('refresh');
                            showAlert(this.messages.good, 'success');
                        }
                    });

                }
            }
        };
        function showModal(title, row) {
            row = row || {
                        id: '',
                        name: '',
                        message: '',
                        type: '',
                        status: '0',
                        tags_list:[]
                    }; // default row value
            $modal.data('id', row.id);
            $modal.find('.modal-title').text(title);
            //$modal.find('select[name="tags"]').val(row.tags_list);
            $('select[name="tags"]')[0].selectize.setValue(row.tags_list);
            for (var name in row) {
                $modal.find('input[name="' + name + '"]').val(row[name]);
                if(name == 'message') {
                    CKEDITOR.instances['message'].setData(row[name]);
                }
//                $modal.find('input[type="checkbox" name="' + name + '"]').prop('checked', (row[name] == "1") ? true : false);
                $modal.find('input[type="checkbox"]').filter('[name="'+name+'"]').prop('checked', (row[name] == "1") ? true : false);
                //$modal.find('checkbox[name="' + name + '"]').val(row[name]);
            }
            $modal.modal('show');
        }
        function showAlert(title, type) {
            $alert.attr('class', 'alert alert-' + type || 'success')
                    .html('<i class="glyphicon glyphicon-check"></i> ' + title).show();
            setTimeout(function () {
                $alert.hide();
            }, 5000);
        }
    </script>
    <script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
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
        })
    </script>
    {{--@foreach($js_files as $js)--}}
            {{--{{$js}}--}}
    {{--@endforeach--}}
@endsection