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
            <h1 class="page-header">Теги</h1>
        </div>
    </div><!--/.row-->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">Список тегов</div>
                <div class="panel-body">
                    <p class="toolbar">
                        <a class="create btn btn-default" href="javascript:">Добавить тег</a>
                    </p>
                    <table id="table" data-toggle="table" data-url="{{URL::to('/bot-telegram/tags')}}" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search="true" data-select-item-name="toolbar1" data-pagination="true" data-sort-name="name" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true" >Ид</th>
                            <th data-field="name" data-sortable="true">Имя</th>
                            <th data-field="frequency" data-sortable="true">Частота</th>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary submit">Сохранить</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


    <script>
        var API_URL = 'http://' + location.host + '/bot-telegram/tags';
        var $table = $('#table').bootstrapTable({url: API_URL}),
                $modal = $('#modal').modal({show: false}),
                $alert = $('.alert').hide();

        function actionFormatter(value) {
            return [
                '<a class="update" href="javascript:" title="Update Item"><i class="glyphicon glyphicon-edit"></i></a>',
                '<a class="remove" href="javascript:" title="Delete Item"><i class="glyphicon glyphicon-remove-circle"></i></a>',
            ].join('');
        }

        $('.create').click(function () {
            showModal($(this).text());
        });

        window.actionEvents = {
            'click .update':function(e, value, row) {
                showModal($(this).attr('title'), row);
            },
            'click .remove': function (e, value, row) {
                if (confirm('Вы уверены что хотите удалить тег?')) {

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
        function showModal(title, row) {

            row = row || {
                        id: '',
                        name:''
                    }; // default row value
            $modal.data('id', row.id);
            $modal.find('.modal-title').text(title);
            for (var name in row) {
                $modal.find('input[name="' + name + '"]').val(row[name]);
            }

            $modal.modal('show');
        }

        $(document).on("click", ".submit", function () {
            var row = {};

            var name = $modal.find('input[name="name"]').val();
            row.name = name;
            //row.name = 'test';
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

    </script>
@endsection
