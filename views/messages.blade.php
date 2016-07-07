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
                    <table id="table" data-toggle="table" data-url="{{URL::to('/bot-telegram/messages')}}" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-pagination="true" data-sort-name="name" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="message_id" data-sortable="true" >Ид внешний</th>
                            <th data-field="from" data-sortable="true">От кого</th>
                            <th data-field="text"  data-sortable="false">Текст</th>
                            <th data-field="command" data-sortable="true">Комманда</th>
                            <th data-field="approved" data-sortable="true">Отправлен ответ</th>
                            <th data-field="answer" data-sortable="false">Ответ</th>
                            <th data-field="type" data-sortable="true">Тип</th>
                            <th data-field="date" data-sortable="true">Время отправки</th>
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

@endsection

<script>

    function actionFormatter(value) {
        return [
            '<a class="remove" href="javascript:" title="Delete Item"><i class="glyphicon glyphicon-remove-circle"></i></a>',
        ].join('');
    }

    window.actionEvents = {
        'click .remove': function (e, value, row) {
            if (confirm('Вы уверены что хотите удалить сообщение?')) {

                var API_URL = window.location.protocol+'//' + location.host + '/bot-telegram/messages';

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
        }
    };

</script>