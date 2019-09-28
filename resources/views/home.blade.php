@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @if($userHasOwnedOrSubscribedLessons)
                    <div class="panel panel-default">
                        <div class="panel-heading">Your lessons</div>
                        <div class="panel-body">
                            <div class="col-md-8 no-padding">
                                <p class="lead">
                                    Lessons created by you and lesson you subscribe.
                                </p>
                                <p>
                                    <a href="/lessons/create" class="btn btn-success" role="button">
                                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                        Create new lesson
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-4 no-padding">
                                <form action="/exercises/search" method="GET">
                                    <div class="input-group">
                                        <input id="search-phrase-input" name="phrase" type="text" class="form-control" placeholder="search.." value="{{ $phrase ?? '' }}">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="submit">
                                                <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                            </button>
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @if(isset($ownedLessons) && !empty($ownedLessons))
                            @foreach($ownedLessons as $row)
                                <div class="col-sm-6 col-md-6">
                                    <div class="thumbnail">
                                        <div class="caption">
                                            <h4>
                                                <span class="glyphicon glyphicon-education" aria-hidden="true"></span>
                                                <a href="/lessons/{{ $row->id }}">{{ $row->name }}</a>
                                            </h4>
                                            <p>
                                                Number of exercises: {{ $row->all_exercises->count() }} </br>
                                                Number of subscribers: {{ $row->subscribers->count() }} </br>
                                            </p>
                                            <p>
                                                @can('learn', $row)
                                                    <a href="/learn/lessons/{{ $row->id }}" class="btn btn-primary margin-bottom"
                                                       role="button">
                                                        <span class="glyphicon glyphicon-play" aria-hidden="true"></span>
                                                        Start
                                                    </a>
                                                @endcan
                                                <a href="/lessons/{{ $row->id }}/exercises/create" class="btn btn-success margin-bottom" role="button">
                                                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                                    Add exercise
                                                </a>
                                                <a href="/lessons/{{ $row->id }}/exercises" class="btn btn-default margin-bottom">
                                                    <span class="glyphicon glyphicon-th" aria-hidden="true"></span>
                                                    Exercises
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if(isset($subscribedLessons) && !empty($subscribedLessons))
                            @foreach($subscribedLessons as $row)
                                <div class="col-sm-6 col-md-6">
                                    <div class="thumbnail">
                                        <div class="caption">
                                            <h4>
                                                <span class="glyphicon glyphicon-education" aria-hidden="true"></span>
                                                <a href="/lessons/{{ $row->id }}">{{ $row->name }}</a>
                                            </h4>
                                            <p>
                                                Number of exercises: {{ $row->all_exercises->count() }} </br>
                                                Number of subscribers: {{ $row->subscribers->count() }} </br>
                                            </p>
                                            <p>
                                                <a href="/learn/lessons/{{ $row->id }}"
                                                   class="btn btn-primary margin-bottom"
                                                   role="button">
                                                    <span class="glyphicon glyphicon-play" aria-hidden="true"></span>
                                                    Start
                                                </a>
                                                <button type="submit" form="unsubscribe-{{ $row->id }}"
                                                        class="btn btn-danger margin-bottom">
                                                <span class="glyphicon glyphicon-remove-sign"
                                                      aria-hidden="true"></span>
                                                    Unsubscribe
                                                </button>
                                                <a href="/lessons/{{ $row->id }}/exercises" class="btn btn-default margin-bottom">
                                                    <span class="glyphicon glyphicon-th" aria-hidden="true"></span>
                                                    Exercises
                                                </a>
                                            <form id="unsubscribe-{{ $row->id }}"
                                                  action="/lessons/{{ $row->id }}/unsubscribe"
                                                  method="POST">
                                                {{ csrf_field() }}
                                            </form>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif

                <div class="panel panel-default">
                    <div class="panel-heading">Available lessons</div>
                    <div class="panel-body">
                        <div class="col-md-8 no-padding">
                            <p class="lead">
                                Browse lessons created by others users.
                            </p>
                            <p>
                                @if(!$userHasOwnedOrSubscribedLessons)
                                    <a href="/lessons/create" class="btn btn-success" role="button">
                                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                        Create your own lesson
                                    </a>
                                @endif
                                <a href="#" class="btn btn-default" role="button">
                                    <span class="glyphicon glyphicon-book" aria-hidden="true"></span>
                                    All available lessons
                                </a>
                            </p>
                        </div>
                        <div class="col-md-4 no-padding">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search available lessons...">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button">
                                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @if(isset($availableLessons) && !empty($availableLessons))
                        @foreach($availableLessons as $row)
                            <div class="col-sm-6 col-md-6">
                                <div class="thumbnail">
                                    <div class="caption">
                                        <h4>
                                            <span class="glyphicon glyphicon-education" aria-hidden="true"></span>
                                            <a href="/lessons/{{ $row->id }}">{{ $row->name }}</a>
                                        </h4>
                                        <p>
                                            Number of exercises: {{ $row->all_exercises->count() }} </br>
                                            Number of subscribers: {{ $row->subscribers->count() }} </br>
                                        </p>
                                        <p>
                                            <button type="submit" form="subscribe-and-learn-{{ $row->id }}"
                                                    class="btn btn-primary margin-bottom">
                                                    <span class="glyphicon glyphicon-play"
                                                          aria-hidden="true"></span>
                                                Subscribe and start
                                            </button>
                                            <button type="submit" form="subscribe-{{ $row->id }}"
                                                    class="btn btn-danger margin-bottom">
                                                    <span class="glyphicon glyphicon-ok"
                                                          aria-hidden="true"></span>
                                                Subscribe
                                            </button>
                                            <a href="/lessons/{{ $row->id }}/exercises" class="btn btn-default margin-bottom">
                                                <span class="glyphicon glyphicon-th" aria-hidden="true"></span>
                                                Exercises
                                            </a>
                                        <form id="subscribe-{{ $row->id }}"
                                              action="/lessons/{{ $row->id }}/subscribe"
                                              method="POST">
                                            {{ csrf_field() }}
                                        </form>
                                        <form id="subscribe-and-learn-{{ $row->id }}"
                                              action="/lessons/{{ $row->id }}/subscribe-and-learn"
                                              method="POST">
                                            {{ csrf_field() }}
                                        </form>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
