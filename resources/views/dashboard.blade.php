@extends('layouts.app')

@section('content')
	<main class="app-main container">
		<div class="app-content-header">
			<div class="container-fluid">
				<div class="row my-3">
					<div class="col-sm-12">
						<div class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<h3 class="mb-0 fw-bold text-uppercase" style="color: black;">DASHBOARD</h3>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


		<div class="app-content">
			<div class="container-fluid">
				<x-notification />
				<div class="row mb-4">
					<div class="col-sm-12 col-md-4 col-6 mb-3">
						<div class="small-box" style="background-color: #eb6cd1 !important; color: #fff;">
							<div class="inner">
								<p>TỔNG KHÁCH HÀNG</p>
								<h3 class="fw-bold" style="font-size: 42px;">12</h3>
							</div>

							<svg class="small-box-icon" fill="rgba(255,255,255,0.2)" viewBox="0 0 24 24"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z">
								</path>
							</svg>

							<div class="small-box-footer-placeholder"></div>
						</div>
					</div>

					<div class="col-sm-12 col-md-4 col-6 mb-3">
						<div class="small-box" style="background-color: #f39c12 !important; color: #fff;">
							<div class="inner">
								<p>TỔNG ACCOUNTANT</p>
								<h3 class="fw-bold" style="font-size: 42px;">3</h3>
							</div>
							<svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M20 7h-4V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2H4a2 2 0 00-2 2v11a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zm-8-2h4v2h-4V5zm8 16H4v-5h16v5zm0-7H4V9h3v1h2V9h6v1h2V9h3v4z">
								</path>
							</svg>
							<div class="small-box-footer-placeholder"></div>
						</div>
					</div>

					<div class="col-sm-12 col-md-4 col-6 mb-3">
						<div class="small-box" style="background-color: #8e44ad !important; color: #fff;">
							<div class="inner">
								<p>USER APP</p>
								<h3 class="fw-bold" style="font-size: 42px;">5</h3>
							</div>
							<svg class="small-box-icon" fill="rgba(255,255,255,0.2)" viewBox="0 0 24 24"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M17 2H7c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-5 17.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM17 14H7V5h10v9z">
								</path>
							</svg>
							<div class="small-box-footer-placeholder"></div>
						</div>
					</div>

					<div class="col-sm-12 col-md-4 col-6 mb-3">
						<div class="small-box h-90" style="background-color: #e74c3c !important; color: #fff;">
							<div class="inner">
								<p>FILE IMPORT CHỜ XỬ LÝ</p>
								<h3 class="fw-bold" style="font-size: 42px;">10</h3>
							</div>
							<svg class="small-box-icon" fill="rgba(255,255,255,0.2)" viewBox="0 0 24 24"
								xmlns="http://www.w3.org/2000/svg">
								<path d="M5 20h14v-2H5v2zm0-10h4v6h6v-6h4l-7-7-7 7z">
								</path>
							</svg>
							<div class="small-box-footer-placeholder"></div>
						</div>
					</div>

					<div class="col-sm-12 col-md-4 col-6 mb-3">
						<div class="small-box h-90" style="background-color: #27ae60 !important; color: #fff;">
							<div class="inner">
								<p>HỒ SƠ MỞ LẠI</p>
								<h3 class="fw-bold" style="font-size: 42px;">15</h3>
							</div>
							<svg class="small-box-icon" fill="rgba(255,255,255,0.2)" viewBox="0 0 24 24"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z">
								</path>
							</svg>
							<div class="small-box-footer-placeholder"></div>
						</div>
					</div>

					<div class="col-sm-12 col-md-4 col-6 mb-3">
						<div class="small-box h-90" style="background-color: #3498db !important; color: #fff;">
							<div class="inner">
								<p>TỜ KHAI CHỜ DUYỆT</p>
								<h3 class="fw-bold" style="font-size: 42px;">20</h3>
							</div>
							<svg class="small-box-icon" fill="rgba(255,255,255,0.2)" viewBox="0 0 24 24"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z">
								</path>
							</svg>
							<div class="small-box-footer-placeholder"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>
@endsection

<style>
	.small-box {
		border-radius: 30px;
		overflow: hidden;
		transition: transform .3s;
		border: none;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		display: flex;
		flex-direction: column;
		min-height: 170px;
	}

	.small-box:hover {
		transform: translateY(-5px);
	}

	.small-box .inner {
		padding: 12px !important;
	}

	.small-box .inner p {
		font-weight: 600;
		text-transform: uppercase;
		font-size: 0.9rem;
		opacity: 0.9;
	}

	.small-box-footer {
		background: rgba(0, 0, 0, 0.1) !important;
		transition: all .3s;
	}

	.small-box-footer:hover {
		font-weight: 600;
		transform: translateY(-2px);
	}

	.small-box-footer,
	.small-box-footer-placeholder {
		margin: 0px 12px 12px 12px;
		border-radius: 8px;
		min-height: 42px;
	}
</style>