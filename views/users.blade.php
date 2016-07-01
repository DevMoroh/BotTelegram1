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
                <h1 class="page-header">Dashboard</h1>
            </div>
        </div><!--/.row-->

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Advanced Table</div>
                    <div class="panel-body">
                        <table data-toggle="table" data-url="{{URL::to('/bot-telegram/users')}}" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-pagination="true" data-sort-name="name" data-sort-order="desc">
                            <thead>
                                <tr>
                                    <th data-field="id" data-checkbox="true" >Ид</th>
                                    <th data-field="username" data-sortable="true">Никнейм</th>
                                    <th data-field="first_name"  data-sortable="true">Имя</th>
                                    <th data-field="last_name" data-sortable="true">Фамилия</th>
                                    <th data-field="subscribe" data-sortable="true">Подписан на рассылку</th>
                                    <th data-field="external_id" data-sortable="true">Внешний ид</th>
                                    <th data-field="service" data-sortable="true">Сервис</th>
                                    <th data-field="time_create" data-sortable="true">Время добавления</th>
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
            if (confirm('Вы уверены что хотите удалить юзера?')) {

                var API_URL = 'http://' + location.host + '/bot-telegram/users';

                var $table = $('#table').bootstrapTable({url: API_URL}),
                        $modal = $('#modal').modal({show: false}),
                        $alert = $('.alert').hide();
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' } });
                $.ajax({
                    url: API_URL + '/' + row.id,
                    type: 'delete',
                    success: function () {
                        $table.bootstrapTable('refresh');
                        showAlert('Delete item successful!', 'success');
                    },
                    error: function () {
                        showAlert('Delete item error!', 'danger');
                    }
                })
            }
        }
    };

</script>