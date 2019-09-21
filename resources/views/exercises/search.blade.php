@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Search for exercises</div>
                    <div class="panel-body">
                        <div class="col-md-4 no-padding margin-bottom">
                            <form action="/exercises/search" method="GET">
                                <div class="input-group">
                                    <input id="search-phrase-input" name="phrase" type="text" class="form-control" placeholder="search.." value="{{ $phrase }}">
                                    <span class="input-group-btn">
                                    <button id="search-phrase-input-button" class="btn btn-default" type="submit">
                                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                    </button>
                                </span>
                                </div>
                            </form>
                        </div>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-bordred table-striped">
                                <thead>
                                <th>Question</th>
                                <th>Answer</th>
                                <th>Lesson</th>
                                </thead>
                                <tbody>
                                @foreach($exercises as $row)
                                    <tr>
                                        <td>
                                            {{ $row->question }}
                                        </td>
                                        <td>
                                            {{ $row->answer }}
                                        </td>
                                        <td>
                                            <a href="/lessons/{{ $row->lesson->id }}">{{ $row->lesson->name }}</a>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="delete-exercise-{{ $row->id }}" tabindex="-1"
                                         role="dialog"
                                         aria-labelledby="delete" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-hidden="true">
                                                        <span class="glyphicon glyphicon-remove"
                                                              aria-hidden="true"></span>
                                                    </button>
                                                    <h4 class="modal-title custom_align">Delete</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-danger">
                                                        <span class="glyphicon glyphicon-warning-sign"></span>
                                                        Are you sure you want to delete?
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" form="delete-exercise-form-{{ $row->id }}" class="btn btn-success">
                                                        <span class="glyphicon glyphicon-ok-sign"></span> Yes
                                                    </button>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">
                                                        <span class="glyphicon glyphicon-remove"></span> No
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <form id="delete-exercise-form-{{ $row->id }}" action="/exercises/{{ $row->id }}" method="POST">
                                            <input name="_method" type="hidden" value="DELETE">
                                            {{ csrf_field() }}
                                        </form>
                                    </div>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script type="text/javascript" language="JavaScript">
        $(document).ready(function () {
            $("#search-phrase-input").focus();
        });
    </script>
@endpush
