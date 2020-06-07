@extends('layouts.event.layout_event')
@section('contentData')
 	<!-- about-block
		================================================== -->
		<section class="contact-page">
			<div class="container">
				<span class="contact-page__short-title">
				</span>
				<h1 class="contact-page__title">
					Contact Us
				</h1>
				<div class="row">
					<div class="col-lg-8 col-md-8">
						
						<!-- Contact form module -->
						<form class="contact-form" id="contact-form">
							<h2 class="contact-form__title">
								Contact Form
							</h2>
							<div class="row">
								<div class="col-md-6">
									<input class="contact-form__input-text" type="text" name="name" id="name" placeholder="Name:" />
								</div>
								<div class="col-md-6">
									<input class="contact-form__input-text" type="text" name="mail" id="mail" placeholder="Email:" />
								</div>
							</div>
							<input class="contact-form__input-text" type="text" name="subject" id="subject" placeholder="Subject" />
							<textarea class="contact-form__textarea" name="comment" id="comment" placeholder="Message"></textarea>
							<input class="contact-form__submit" type="submit" name="submit-contact" id="submit_contact" value="Submit Message" />
						</form>
						<!-- End Contact form module -->

					</div>

					<div class="col-lg-3 offset-lg-1 col-md-4">

						<!-- contact-post-module -->
						<div class="contact-post">
							<i class="la la-map-marker"></i>
							<div class="contact-post__content">
								<h2 class="contact-post__title">
									Location:
								</h2>
								<p class="contact-post__description">
									E-171, First Floor Noida, Sector 63, Noida, Uttar Pradesh 201301
								</p>
							</div>
						</div>
						<!-- End contact-post-module -->

						<!-- contact-post-module -->
						<div class="contact-post">
							<i class="la la-phone"></i>
							<div class="contact-post__content">
								<h2 class="contact-post__title">
									Phone:
								</h2>
								<p class="contact-post__description">
									+91 9818672306
								</p>
							</div>
						</div>
						<!-- End contact-post-module -->

						<!-- contact-post-module -->
						<div class="contact-post">
							<i class="la la-envelope"></i>
							<div class="contact-post__content">
								<h2 class="contact-post__title">
									Email:
								</h2>
								<p class="contact-post__description">
									nexzoa@gmail.com
								</p>
							</div>
						</div>
						<!-- End contact-post-module -->

					</div>

				</div>

			</div>
		</section>
		<!-- End contact-page-block -->
                    
@endsection
