@include('layouts.header')
<main>
		<div id="breadcrumb">
			<div class="container">
				<ul>
					<li><a href="#">Home</a></li>
					<li><a href="#">Category</a></li>
					<li>Page active</li>
				</ul>
			</div>
		</div>
		<!-- /breadcrumb -->

		<div class="container margin_60">
			<div class="row">
				<aside class="col-lg-3" id="sidebar">
						<div class="box_style_cat" id="faq_box">
							<ul id="cat_nav">
								<li><a href="#payment" class="active"><i class="icon_document_alt"></i>
                                                                        Professional Statement</a></li>
								<li><a href="#tips"><i class="icon_document_alt"></i>Educatioin</a></li>
								<li><a href="#reccomendations"><i class="icon_document_alt"></i>Price & payments</a></li>
								<li><a href="#terms"><i class="icon_document_alt"></i>Terms&amp;conditons</a></li>
								<li><a href="#booking"><i class="icon_document_alt"></i>Bookings</a></li>
							</ul>
						</div>
						<!--/sticky -->
				</aside>
				<!--/aside -->
				
				<div class="col-lg-9" id="faq">
					<div role="tablist" class="add_bottom_45 accordion" id="payment">
						<div class="card">
							<div class="card-header" role="tab">
								<h5 class="mb-0">
									<a data-toggle="collapse" href="#collapseOne_payment" aria-expanded="true"><i class="indicator icon_minus_alt2"></i>Professional Statement</a>
								</h5>
							</div>

							<div id="collapseOne_payment" class="collapse show" role="tabpanel" data-parent="#payment">
								<div class="card-body">
                                                                    <div class="container">
			<div class="row">
				<div class=" col-lg-12">
				<div class="box_general_3 cart">
					<div class="form_title">
						<h3><strong><i class="indicator icon_minus_alt2"></i></strong>Your Details</h3>
						<p>
							Mussum ipsum cacilds, vidis litro abertis.
						</p>
					</div>
					<div class="step">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="form-group">
									<label>First name</label>
									<input type="text" class="form-control" id="firstname_booking" name="firstname_booking" placeholder="Jhon">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="form-group">
									<label>Last name</label>
									<input type="text" class="form-control" id="lastname_booking" name="lastname_booking" placeholder="Doe">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="form-group">
									<label>Email</label>
									<input type="email" id="email_booking" name="email_booking" class="form-control" placeholder="jhon@doe.com">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="form-group">
									<label>Confirm email</label>
									<input type="email" id="email_booking_2" name="email_booking_2" class="form-control" placeholder="jhon@doe.com">
								</div>
							</div>
						</div>
                                            <div class="row">
							<div class="col-md-6 col-sm-6">
								<label>Country</label>
								<div class="form-group">
									<select class="form-control" name="country" id="country">
										<option value="">Select your country</option>
										<option value="Europe">Europe</option>
										<option value="United states">United states</option>
										<option value="Asia">Asia</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="form-group">
									<label>Telephone</label>
									<input type="text" id="telephone_booking" name="telephone_booking" class="form-control" placeholder="00 44 678 94329">
								</div>
							</div>
						</div>
                                           
                                            <!--End step -->
					<div id="policy">
						<h4>Cancellation policy</h4>
						<div class="form-group">
							<div class="checkboxes">
								<label class="container_check">I accept <a href="#0">terms and conditions and general policy</a>
									<input type="checkbox">
									<span class="checkmark"></span>
								</label>
							</div>
						</div>
					</div>
					</div>
					<hr>
                                         <div class="row">
                                            <input class="btn_1" type="submit" value="Submit">
                                            </div>
					<!--End step -->
				</div>
				</div>
				<!-- /col -->
                                    
			</div>
                                                                        
			<!-- /row -->
		</div>  
								</div>
							</div>
						</div>
						<!-- /card -->
						<!-- /card -->
						<!-- /card -->
					</div>
                                    <div role="tablist" class="add_bottom_45 accordion" id="specification">
						<div class="card">
							<div class="card-header" role="tab">
								<h5 class="mb-0">
									<a data-toggle="collapse" href="#collapseOne_specification" aria-expanded="true"><i class="indicator icon_minus_alt2"></i>Specification </a>
								</h5>
							</div>

							<div id="collapseOne_specification" class="collapse show" role="tabpanel" data-parent="#specification">
								<div class="card-body">
                                                                    <div class="container">
			<div class="row">
				<div class=" col-lg-12">
				<div class="box_general_3 cart">
					<div class="form_title">
						<h3><strong><i class="indicator icon_minus_alt2"></i></strong>Your Details</h3>
						<p>
							Mussum ipsum cacilds, vidis litro abertis.
						</p>
					</div>
					<div class="step">
						<div class="row">
							<div class="col-md-9 col-sm-9">
								<div class="form-group">
									<label>Specification</label>
                                                                        <input type="text" class="form-control" id="firstname_booking" name="firstname_booking" placeholder="Jhon"><br>
                                                                        <span class="btn_1"><strong>Add More</strong></span>
								</div>
							</div>
						</div>
                                            <!--End step -->
					</div>
					<hr>
                                         <div class="row">
                                            <input class="btn_1" type="submit" value="Submit">
                                            </div>
					<!--End step -->
				</div>
				</div>
				<!-- /col -->
                                    
			</div>
                                                                        
			<!-- /row -->
		</div>  
								</div>
							</div>
						</div>
						<!-- /card -->
						<!-- /card -->
						<!-- /card -->
					</div>
					<!-- /accordion payment -->
				</div>
                                
				<!-- /col -->
			</div>
			<!-- /row -->
		</div>
		<!-- /container -->
	</main>
	<!-- /main -->
	<!-- /main -->
@include('layouts.footer')