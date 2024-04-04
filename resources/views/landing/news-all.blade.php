{{-- @extends('preview.iziklaim.landing.app') --}}
@extends('landing-side')


@section('content')
    <style>
        .text-justify {
            text-align: justify;
        }

        .text-hover-primary:hover {
            color: blue;
            /* Change text color to blue on hover */
            /* Additional styles for hover state if needed */
        }

        .text-dark {
            color:
                black;
            /* Additional styles for dark text */
        }
    </style>
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="container">
                <div class="d-flex flex-column flex-xl-row">
                    <div class="flex-lg-row-fluid me-xl-15">

                        <div class="mb-17 text-center">

                            <div class="mb-8">
                                <div class="overlay mb-2">
                                    <div class="bgi-no-repeat bgi-position-center bgi-size-cover card-rounded">
                                        <img class="img-fluid rounded" src="{{ $news[0]['images'] }}" alt="">
                                    </div>
                                </div>
                                <p href="#" class="text-dark text-justify fs-3 fw-bold">{{ $news[0]['title'] }}</p>
                            </div>
                            <div class=" text-justify">
                                <p>
                                    <a href="{{ route('news.detail', $news[0]['slug']) }}">Baca
                                        selengkapnya</a>
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 mb-5">
                            <div class="row g-10">
                                @for ($i = 1; $i < count($news); $i++)
                                    <div class="col-md-4">
                                        <div class="card-xl-stretch">
                                            <a
                                                href="{{ $news[$i]['check'] == null ? url('view/news/iziklaim/' . $news[$i]['slug']) : $news[$i]['check'] }}">
                                                <img class="img-fluid rounded w-100" src="{{ $news[$i]['images'] }}"
                                                    alt="">
                                            </a>
                                            <div class="mt-3">
                                                <a href="{{ $news[$i]['check'] == null ? url('view/news/iziklaim/' . $news[$i]['slug']) : $news[$i]['check'] }}"
                                                    style="color: black; font-weight: bold">{{ $news[$i]['title'] }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
