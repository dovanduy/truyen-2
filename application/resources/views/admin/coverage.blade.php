@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Coverage')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Coverage')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover table-ultra-responsive">
                                <thead>
                                <tr>
                                    <th style="width: 5%;">{{language_data('SL')}}#</th>
                                    <th style="width: 20%;">{{language_data('Country')}}</th>
                                    <th style="width: 15%;">{{language_data('ISO Code')}}</th>
                                    <th style="width: 15%;">{{language_data('Country Code')}}</th>
                                    <th style="width: 15%;">{{language_data('Tariff')}}</th>
                                    <th style="width: 10%;">{{language_data('Status')}}</th>
                                    <th style="width: 20%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($country_codes as $cc)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Country"><p>{{$cc->country_name}}</p></td>
                                        <td data-label="ISO Code"><p>{{$cc->iso_code}}</p></td>
                                        <td data-label="Country Code"><p>{{$cc->country_code}}</p></td>
                                        <td data-label="Tariff"><p>{{$cc->tariff}}</p></td>
                                        @if($cc->active=='1')
                                            <td data-label="Status"><p class="label label-success">{{language_data('Live')}}</p></td>
                                        @else
                                            <td data-label="Status"><p class="label label-danger">{{language_data('Offline')}}</p></td>
                                        @endif
                                        <td data-label="Actions">
                                            <a class="btn btn-success btn-xs" href="{{url('sms/manage-coverage/'.$cc->id)}}" ><i class="fa fa-edit"></i> {{language_data('Manage')}}</a>
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

@endsection

{{--External Style Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/libs/data-table/datatables.min.js")!!}

    <script>
        $(document).ready(function(){
            $('.data-table').DataTable();
        });
    </script>
@endsection