<div style="margin: 10px 20px 0px 20px">
	<div class="card">
		<div class="row no-gutters">
			<div class="col-md-3">
				<div style="margin: 10px">
					<img src="{{$detail['logo']}}" width="100%" onerror="this.src = 'https://jikeadmin.saishiyun.net/img/example.png'">
				</div>
			</div>
			<div class="col-md-9">
				<div class="card-body">
					<h5 class="card-title">{{$detail['product_name']}} &nbsp;<span class="text-muted" style="font-size: 10px"> v{{$detail['last_version']}}</span></h5>
					<p class="card-text text-secondary">{{$detail['detail']}}</p>
					<div style="">
						<div class="text-muted "><strong>环境要求:</strong></div>
						<div style="margin-left: 5px">
							@if($detail['tags_require'])
								@foreach ($detail['tags_require'] as $key => $item)
									<div class="card-text"><small class="text-muted"> {{$key}} {{$item}}</small></div>
								@endforeach
							@endif
						</div>
					</div>
					<br/>
					@if($detail['price'] == 0)
					    <a href="#" class="btn btn-success btn-sm card-link">免费</a>
					@else
						<a href="#" class="btn btn-warning btn-sm card-link">￥ {{$detail['price']}}</a>
					@endif
					<div style="width: 300px;position: absolute;top: 5px;right: 15px;">
						<div class="card card-widget widget-user">

							<div class="widget-user-header bg-info" style="height:80px;background-color: #17a2b8!important">
								<h3 class="widget-user-username">{{$detail['author']}}</h3>
							</div>
							<div class="widget-user-image" style="top:60px;margin-left:-15px !important;">
								<img class="img-circle elevation-2" style="width: 40px" src="https://adminlte.io/themes/v3/dist/img/user1-128x128.jpg">
							</div>
							<div class="card-footer" style="margin:0px !important;padding-top:15px !important;">
								<div class="row">
									<div class="col-sm-4 border-right">
										<div class="description-block">
											<h5 class="description-header">{{$detail['product_num'] ?? '-'}}</h5>
											<span class="description-text">作品</span>
										</div>
									</div>

									<div class="col-sm-4 border-right">
										<div class="description-block">
											<h5 class="description-header">{{$detail['down_num_total'] ?? '-'}}</h5>
											<span class="description-text">下载量</span>
										</div>

									</div>

									<div class="col-sm-4">
										<div class="description-block">
											<h5 class="description-header">-</h5>
											<span class="description-text">关注度</span>
										</div>

									</div>

								</div>

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>