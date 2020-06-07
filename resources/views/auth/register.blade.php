@extends('layouts.event.layout_event')
@section('contentData')
		<section class="sign">
                    <div class="container">
                        <div class="row justify-content-center" >
        <div class="col-md-8" >
                    <div class="sign__area">
				<nav>
					<div class="nav nav-tabs" id="nav-tab" role="tablist">
						<!--<a class="nav-item nav-link" id="nav-sign-tab" data-toggle="tab" href="#nav-sign" role="tab" aria-controls="nav-sign" aria-selected="false">Sign in</a>-->
						<a class="nav-item nav-link active" id="nav-register-tab" data-toggle="tab" href="#nav-register" role="tab" aria-controls="nav-register" aria-selected="true">Register</a>
					</div>
				</nav>
				<div class="tab-content" id="nav-tabContent">
					<div class="tab-pane fade" id="nav-sign" role="tabpanel" aria-labelledby="nav-sign-tab">

						<!-- sign-form-module -->
<!--						<form class="sign-form">
							<label class="sign-form__label" for="username">
								Email address or Username:
							</label>
							<input class="sign-form__input-text" type="text" name="username" id="username" placeholder="Email or Username">
							<label class="sign-form__label" for="password">
								Password:
							</label>
							<input class="sign-form__input-text" type="password" name="password"     placeholder="Password">
							<div class="sign-form__checkbox">
								<input class="sign-form__input-checkbox" type="checkbox" name="rememb-check" id="rememb-check">
								<span class="sign-form__checkbox-style"></span>
								<span class="sign-form__checkbox-text">Remember me</span>
							</div>
							<a class="sign-form__forget-link" href="#">Forget password?</a>

							<button class="sign-form__submit" id="submit-loggin" type="submit">
								<i class="fa fa-sign-in" aria-hidden="true"></i>
								Login
							</button>
							<p class="sign-form__text">
								or Login With
							</p>
							<ul class="sign-form__social">
								<li><a href="#" class="facebook"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
								<li><a href="#" class="google"><i class="fa fa-google" aria-hidden="true"></i></a></li>
							</ul>
						</form>-->

						<!-- End sign-form-module -->

					</div>
					<div class="tab-pane fade active show"  id="nav-register" role="tabpanel" aria-labelledby="nav-register-tab">

                @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong> Please correct errors and try again!.
                                                    <br/>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                                            
			<div class="errorMsg"> </div>
                    <form method="POST" class="register" action="{{ route('save_profile') }}">
                        @csrf
							 <input id="email" placeholder='Enter Email Address' type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                                           @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                                         <br>
							 <input id="phone" placeholder='Enter Phone' type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required autocomplete="phone">

                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <br>
			        <input id="password" placeholder='Password' type="password" value="" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                             <br>
                                <input id="password_confirmation" placeholder='Confirm Password' type="password" value=""  class="form-control" name="password_confirmation" required autocomplete="new-password">
                                <div class="sign-form__checkbox">
                                        <input class="sign-form__input-checkbox" type="checkbox" name="rememb-check2" id="rememb-check2">
                                        <span class="sign-form__checkbox-style"></span>
                                        <span class="sign-form__checkbox-text">I've read and accept terms &amp; conditions</span>
                                </div>
                                <div id="loader"></div>
                                <button class="sign-form__submit submit" type="button" >
                                        <i class="fa fa-sign-in" aria-hidden="true"></i>
                                        Sign Up
                                </button>
						</form>

						<!-- End sign-form-module -->
					</div>
				</div>
			</div>
        </div>
        </div>
        </div>
			<div class="sign__background"></div>
                        
                        
		</section>
 
@include('otp::otp')

@endsection
