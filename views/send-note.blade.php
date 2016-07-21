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
            <h1 class="page-header">Сообщения</h1>
        </div>
    </div><!--/.row-->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">Список сообщений</div>
                <div class="panel-body">
                    <table id="table" data-toggle="table" data-url="{{URL::to('/bot-telegram/send-notes')}}" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-pagination="true" data-sort-name="name" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="notification" data-sortable="true">Уведомление</th>
                            <th data-field="user"  data-sortable="false">Пользователь</th>
                            <th data-field="time_send" data-sortable="true">Время отправки</th>
                            <th data-field="status_send" data-formatter="statusDisplay" data-sortable="true">Статус</th>
                            <th data-field="counts" data-sortable="false">Количество отправок</th>
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

    <div id="modal-users" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="alert"></div>
                <div class="modal-body">
                    <table id="table-users" data-show-toggle="true" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-pagination="true" data-sort-name="name" data-sort-order="desc">
                        <thead>
                            <tr>
                                <th data-field="notification"  data-formatter="notificationFormatter" data-sortable="true">Уведомление</th>
                                <th data-field="user" data-formatter="userFormatter" data-sortable="false">Пользователь</th>
                                <th data-field="message" data-sortable="false">Статус</th>
                            </tr>
                        </thead>

                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary submit">Сохранить</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <script>
        var API_URL_USERS = window.location.protocol+'//' + location.host + '/bot-telegram/send_users';
        var $table_users = $('#table-users').bootstrapTable();
        var $modal_users = $('#modal-users').modal({show: false});


        function notificationFormatter(value) {
            var n = value.name;
            return n;
        }

        function userFormatter(value) {
            var n = value.first_name+' '+value.last_name;
            return n;
        }

        function actionFormatter(value) {
            return [
                '<a class="update" href="javascript:" title="Update Item"><i class="glyphicon glyphicon-edit"></i></a>',
                '<a class="remove" href="javascript:" title="Delete Item"><i class="glyphicon glyphicon-remove-circle"></i></a>',
            ].join('');
        }

        function statusDisplay(value) {
            var status = '';
            switch(value) {
                case 0:
                    status = 'Не запущено';
                    break;
                case 1:
                    status = 'Запущено';
                    break;
                case 2:
                    status = 'Выполнено';
                    break;
            }

            return [
                status,
            ].join('');
        }

        window.actionEvents = {
            'click .remove': function (e, value, row) {
                if (confirm('Вы уверены что хотите удалить запись?')) {

                    var API_URL = window.location.protocol+'//' + location.host + '/bot-telegram/send-notes';

                    var $table = $('#table').bootstrapTable({url: API_URL}),
                            $modal = $('#modal').modal({show: false}),
                            $alert = $('.alert').hide();
                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' } });

                    ajaxSend({
                        type:'delete',
                        messages:{ bad:'Ошибка удаления!', good:'Удаление прошло успешно!'},
                        url:API_URL + '/' + row.id,
                        success:function() {
                            $modal.modal('hide');
                            $table.bootstrapTable('refresh');
                        }
                    });
                }
            },

            'click .update':function(e, value, row) {
                showModal($(this).attr('title'), row);
            }
        };

        function showModal(title, row) {
            var url = API_URL_USERS + '/'+row.id;
            $table_users.bootstrapTable('refresh', {url: url});

            $modal_users.modal('show');
        }

    </script>
@endsection

