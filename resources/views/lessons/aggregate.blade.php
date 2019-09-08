@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @include('shared.manage_lesson')

                <div class="panel panel-default">
                    <div class="panel-heading">Aggregate</div>
                    <div class="panel-body">
                        <div class="table-responsive">

                            <form action="" method="POST">
                                <table class="table table-bordred table-striped">
                                    <thead>
                                    <th>Aggregate</th>
                                    <th>Lesson</th>
                                    </thead>
                                    <tbody>
                                    @foreach($lessons as $lesson)
                                        <tr>
                                            <td>
                                                @if($lesson['is_aggregated'])
                                                    <input name="aggregates[]" type="checkbox" checked="checked" value="{{ $lesson['id'] }}" \>
                                                @else
                                                    <input name="aggregates[]" type="checkbox" value="{{ $lesson['id'] }}" \>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $lesson['name'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-success">
                                    <span class="glyphicon glyphicon-edit"></span> Save
                                </button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
