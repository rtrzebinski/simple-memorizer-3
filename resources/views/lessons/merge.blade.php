@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @include('shared.manage_lesson')

                <div class="panel panel-default">
                    <div class="panel-heading">Merge</div>
                    <div class="panel-body">
                        <div class="table-responsive">

                            <form action="" method="POST">
                                <table class="table table-bordred table-striped">
                                    <thead>
                                    <th>Merge</th>
                                    <th>Lesson</th>
                                    </thead>
                                    <tbody>
                                    @foreach($lessons as $lesson)
                                        <tr>
                                            <td>
                                                <input name="toBeMerged[]" type="checkbox" value="{{ $lesson['id'] }}" \>
                                            </td>
                                            <td>
                                                <a href="/lessons/{{ $lesson['id'] }}">{{ $lesson['name'] }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                {{ csrf_field() }}

                                <button type="submit" class="btn btn-default btn-lg">Submit</button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
