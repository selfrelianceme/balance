@extends('adminamazing::teamplate')

@section('pageTitle', 'Баланс платежных систем')
@section('content')
    <script>
        var route = '{{ route('home') }}';
        var message = 'Вы точно хотите удалить данное сообщение?';
        var routeLoadedBalance = '{{route('AdminBalanceLoad')}}';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.slim.js"></script>

    <script src="{{ asset('vendor/balance/balance.js') }}"></script>
    <style type="text/css">
        .text-purcharse{
            color: #27a10f;
        }
        .text-withdraw{
            color: #c70c0d;
        }
    </style>
    <!-- Daterange picker plugins css -->
    <link href="{{ asset('vendor/adminamazing/assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    @push('scripts')
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
    
        <script src="{{ asset('vendor/adminamazing/assets/plugins/moment/moment.js') }}"></script>
        <!-- Date range Plugin JavaScript -->
        <script src="{{ asset('vendor/adminamazing/assets/plugins/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

        <script type="text/javascript">
            $(function () {
                $('.input-daterange-datepicker').daterangepicker({
                    buttonClasses: ['btn', 'btn-sm'],
                    applyClass: 'btn-danger',
                    cancelClass: 'btn-inverse',
                    locale: {
                        format: 'DD-MM-YYYY'
                    }
                }, function(from, to){
                    window.location.href = "{{route('AdminBalance')}}?from="+from.format('DD-MM-YYYY')+"&to="+to.format('DD-MM-YYYY');
                });
                $('#container').highcharts({
                    title: {
                        text: 'Пополнение/Вывод',
                        x: -20 //center
                    },
                    xAxis: {
                        categories: [
                            @foreach($data_for_chart as $key => $row) 
                                '{{$key}} ({{number($row['purchase']-$row['withdraw'])}})',
                            @endforeach
                        ]
                    },
                     yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    tooltip: {
                        valueSuffix: ' $'
                    },
                    series: [{
                        name: 'Пополнение',
                        color: '#27a10f',
                        data: [
                            @foreach($data_for_chart as $key => $row) {{$row['purchase']}}, @endforeach
                        ]
                    }, {
                        name: 'Вывод',
                        color: '#c70c0d',
                        data: [
                            @foreach($data_for_chart as $key => $row) {{$row['withdraw']}}, @endforeach
                        ]
                    }]
                });
            });
        </script>
    @endpush
    <div class="row">
        <!-- Column -->
        <div class="col-lg-12">
        
            <div class="card">
                <div class="card-block">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3>Статистика пополнений в USD</h3>
                                    <h6 class="card-subtitle">{{$startOfMonth->format('d F Y')}} - {{$endOfMonth->format('d F Y')}}</h6>
                                </div>
                                <div style="width: 300px;">
                                    <input class="form-control input-daterange-datepicker text-center" type="text" name="daterange" value="{{$startOfMonth->format('d/m/Y')}} - {{$endOfMonth->format('d/m/Y')}}" />      
                                </div>
                                <div class="">
                                    <ul class="list-inline">
                                        <li>
                                            <h6 class="text-muted"><i class="fa fa-circle m-r-5 text-purcharse"></i>Пополнения</h6> </li>
                                        <li>
                                            <h6 class="text-muted"><i class="fa fa-circle m-r-5 text-withdraw"></i>Вывод</h6> </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="total-revenue4" id="container" style="height: 350px;"></div>
                        </div>
                        <div class="col-lg-3 col-md-6 m-b-30 m-t-20 text-center">
                            <h1 class="m-b-0 font-light">${{$total_deposits}}</h1>
                            <h6 class="text-muted">Всего пополнено</h6></div>
                        <div class="col-lg-3 col-md-6 m-b-30 m-t-20 text-center">
                            <h1 class="m-b-0 font-light">${{$total_withdraw}}</h1>
                            <h6 class="text-muted">Всего вывода</h6></div>
                        <div class="col-lg-3 col-md-6 m-b-30 m-t-20 text-center">
                            <h1 class="m-b-0 font-light">${{$expenses}}</h1>
                            <h6 class="text-muted">Расходы</h6></div>
                        <div class="col-lg-3 col-md-6 m-b-30 m-t-20 text-center">
                            <h1 class="m-b-0 font-light">${{$total_profit_system}}</h1>
                            <h6 class="text-muted">Заработок</h6></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-block">
                    <h4 class="card-title">Изменить расходы</h4>
                    <form class="form" method="POST" action="{{route('AdminBalanceSaveExpenses')}}">
                        
                        <div class="form-group row {{ $errors->has('EXPENSES') ? ' error' : '' }}">
                            <label for="EXPENSES" class="col-2 col-form-label">Сумма в USD</label>
                            <div class="col-10">
                                <input type="text" name="EXPENSES" value="{{env('EXPENSES')}}" class="form-control" id="EXPENSES"/>                           
                                @if ($errors->has('EXPENSES'))
                                    <div class="help-block"><ul role="alert"><li>{{ $errors->first('EXPENSES') }}</li></ul></div>
                                @endif   
                            </div>
                            
                        </div>

                        <div class="form-group m-b-0">
                            <div class="offset-sm-2 col-sm-9">
                                <button type="submit" class="btn btn-info waves-effect waves-light m-t-10">Сохранить расходы</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-block">
                    <h4 class="card-title">Вывод со статусом ожидание(всего: {{$total_usd_withdraw}} USD)</h4>
                    <div class="row">
                        @foreach($pending_withdraw as $row)
                            <div class="col-6">{{ $row['payment_system']->title }}: {{$row['amount']}} {{$row['payment_system']->currency}}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="row">
        <!-- column -->
        <div class="col-12">
            <div class="card">
                <div class="card-block">
                    <h4 class="card-title pull-left">Баланс </h4>
                    <a href="javascript:void(0)" class="pull-right show_all_balance">Показать все балансы</a>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Платежная система</th>
                                    <th>Баланс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment_system as $row)
                                    <tr>
                                        <td>{{$row->id}}</td>
                                        <td>{{$row->title}}</td>
                                        <td><a data-id="{{$row->id}}" class="loaded_balance" href="javascript:void(0)">Показать</a> {{$row->currency}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- column -->
    </div>

    <div class="row">
        <!-- column -->
        <div class="col-12">
            <div class="card">
                <div class="card-block">
                    <h4 class="card-title pull-left">Пополнения</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Сегодня</th>
                                    <th>Неделя</th>
                                    <th>Месяц</th>
                                    <th>Всего</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purcharse as $row)
                                    <tr>
                                        <td>{{$row["title"]}}, {{$row['currency']}}</td>
                                        <td>{{$row['data']['today']}} {{$row['currency']}}</td>
                                        <td>{{$row['data']['week']}} {{$row['currency']}}</td>
                                        <td>{{$row['data']['month']}} {{$row['currency']}}</td>
                                        <td>{{$row['data']['total']}} {{$row['currency']}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- column -->
    </div>

    <div class="row">
        <!-- column -->
        <div class="col-12">
            <div class="card">
                <div class="card-block">
                    <h4 class="card-title pull-left">Вывод</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Сегодня</th>
                                    <th>Неделя</th>
                                    <th>Месяц</th>
                                    <th>Всего</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($withdraw as $row)
                                    <tr>
                                        <td>{{$row["title"]}}, {{$row['currency']}}</td>
                                        <td>{{$row['data']['today']}} {{$row['currency']}}</td>
                                        <td>{{$row['data']['week']}} {{$row['currency']}}</td>
                                        <td>{{$row['data']['month']}} {{$row['currency']}}</td>
                                        <td>{{$row['data']['total']}} {{$row['currency']}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- column -->
    </div>
@endsection

