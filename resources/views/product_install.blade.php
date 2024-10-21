<style>
	.extension-demo {
		color: @primary;
	}
	.page-content{
		padding: 20px;
	}
	.output-body {
		background: #000000;
		line-height: 2rem;
		padding: 10px;
		border-radius: 0;
		padding: .8rem;
	}
	.box-header{
		justify-content:start;
	}
</style>
{{-- color: #00fa4a; --}}
<div class="terminal-box">
	<div class="output-body">
		<div class="output-main">
			<div>
				<i class="feather icon-minus text-white"></i>
				<span class="text-white">安装模块 {{$items['product_name']}}（ {{$items['package_name']}}） V{{$items['version']}} </span>
			</div>
		</div>

		<div class="output-mainpo" style="display: none">
			<div>
				<i class="feather icon-minus text-white"></i>
				<span class="text-white">安装模块 {{$items['product_name']}}（ {{$items['package_name']}}） V{{$items['version']}} </span>
			</div>
			<div>
				<span class="text-white"><i class="feather icon-minus"></i> 开始安装远程模块 {{$items['package_name']}} V{{$items['version']}}</span>
			</div>
			<div>
				<span class="text-white"><i class="feather icon-minus"></i> 开始模块安装预检...</span>
			</div>
			<div>
				<span class="text-danger"><i class="feather icon-x"></i> 还没有购买该模块，请先 <a target="_blank" href="https://jikeadmin.saishiyun.net/m/MemberDistribution">购买</a> 后重新安装</span>
			</div>
			<div>
				<div class="buy-box card mb-3" style="max-width: 540px;">
					<div class="row no-gutters">
						<div class="col-md-3">
							<img width="140" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAIAAAAiOjnJAAAEKklEQVR4nO3d0W7cNhBA0STo/3+y0deiChqCmEtq3XOevfImviAwoCT+/Pr6+gHTft3+AnxPwiIhLBLCIiEsEsIiISwSwiIhLBLCIiEsEsIi8dfiz/36dTPBvZ3y53d+XmflZ1au/NRd5yP+FlYsEsIiISwSwiIhLBKrU+FTd0/zytSzN809Tc1uK59a+V3dv2LP9gRqxSIhLBLCIiEsEsIisT8VPu1NEN0c1E1873/K9/q/y4pFQlgkhEVCWCSERWJyKjzp7tQzdefn1B2tL2TFIiEsEsIiISwSwiLxqVPh1Dx18inC/xUrFglhkRAWCWGREBaJyanw5GQ09azfipPvjZn6P7w+pVqxSAiLhLBICIuEsEjsT4V334T51N2xObULefedqIe97gvxPQiLhLBICIuEsEj8vL6pNOXkPuDUSRkrPvQPZMUiISwSwiIhLBLCIrE6FXa7Ud15fHdP+pvaK+yunN7RasUiISwSwiIhLBLCIrF6B+nUntrzZ94/K005OaVev6fUikVCWCSERUJYJIRFYvK5wqmp5/pE8y/d3uXez+x9am8e3/auPyHfhrBICIuEsEgIi8T+VNi9KWVqurz7Ps+pifjkHbZ73+f3H9z5RvAnwiIhLBLCIiEsEvvPFZ58f+bdUwWnZtK798oeZsUiISwSwiIhLBLCInH6ZIqTJ8JP7YV13+fkORTeNsN3ICwSwiIhLBLCItHuFT594kkQ3+NEiZP3nf6wYhERFglhkRAWCWGR2N8rfDq5pzZ1ne4NMCfvF13R7UL+/lJTF4J/EhYJYZEQFglhkdg/xb57lu3kvuTe99nT7R6e/N9YZMUiISwSwiIhLBLCIrG6V7gyd7zt5MG9b7ji7hOCe/ubh2dtKxYJYZEQFglhkRAWicmTKfY+NXWu/dS9oFM7gyfvVr17j+vvL773MfhvwiIhLBLCIiEsEpPPFT5NTUbdpLby27vrrLh7OuE2KxYJYZEQFglhkRAWicmp8O4u29R1BiejP165m39XfvsKJ1PwLsIiISwSwiIhLBL7b5u56+S57Z/4u6YmYlMh7yIsEsIiISwSwiIx+baZzsm7MU++SfXklQ+/p9SKRUJYJIRFQlgkhEXi9NtmVnTvKV25zt7PvO0dpCufWnnPz/Zvt2KREBYJYZEQFglhkbj/XOHJ92fePVHiaWpSe6GP/NK8n7BICIuEsEgIi0T7DtK3uf603R+vvOLkPuk2KxYJYZEQFglhkRAWie88FXa7fndPVJySPmloxSIhLBLCIiEsEsIiMTkVvu11pt3phHsnStydJU8+rfnDikVEWCSERUJYJIRFYn8q/MTn3famnr0ZcOpTTyfPPbRXyLsIi4SwSAiLhLBIfOp5hbycFYuEsEgIi4SwSAiLhLBICIuEsEgIi4SwSAiLhLBI/A0n0DVjoiMbjAAAAABJRU5ErkJggg==" alt="...">
							<div class="text-center">微信扫码购买</div>
						</div>
						<div class="col-md-9">
							<div class="card-body">
								<div class="card-package_name">用户分销 标准授权 <span class="text-danger">￥129.99</span></div>
								<div style="clear: both"></div>
								<div class="bg-light" style="padding: 5px">
									<div class="card-text" style="line-height: 18px">
										<div><i class="fa fa-check-circle text-success"></i><small class="text-muted"> 免费更新升级</small></div>
										<div><i class="fa fa-check-circle text-success"></i><small class="text-muted"> 提供源码，私有化独立部署</small></div>
										<div><i class="fa fa-check-circle text-success"></i><small class="text-muted"> 问答社区技术支持</small></div>
										<div><i class="fa fa-check-circle text-success"></i><small class="text-muted"> 正版授权 允许商业使用</small></div>
										<div><i class="fa fa-check-circle text-success"></i><small class="text-muted"> 授权主域名 x 1</small></div>
										<div><i class="fa fa-times-circle text-danger"></i><small class="text-muted"> 禁止转销售模块插件源码</small></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="output-footer">
			<i class="zi zi_circlenotch zi_spin text-white"></i>
			<span class="text-white">当前操作已运行 <n id="miao">0</n> s ...</span>
		</div>
	</div>

</div>
<script data-exec-on-popstate>

</script>

<script>
	var loadtime = 0;

	// 设置定时器
	var intervalId = setInterval(function() {
		loadtime = loadtime + 1;
		$('#miao').text(loadtime);
	}, 1000);

	function LA() {}
	LA.token = "{{e(csrf_token(), false)}}";
	function commandDialogMsgsPush(msg) {
		if (!msg) {
			return
		}
		if (!Array.isArray(msg)) {
			msg = [msg]
		}
		msg = msg.map(m => {
			m = m.trim()
			if (!m.startsWith('<')) {
				m = '<i class="iconfont icon-hr"></i> ' + m
			}
			$('.output-main').append(m);
			//return m
		})

	}

	function doFinish() {
		var htmlss = '<div>\n' +
		'\t\t\t\t<i class="feather icon-check-circle text-success"></i>\n'+
		'\t\t\t\t<span class="text-success">操作已运行完成</span>\n' +
		'\t\t\t</div>';
		$('.output-main').append(htmlss);
	}

	function doCommand(command, data, step, package_name) {
		$.ajax({
			method: 'POST',
			url: '{{ url('admin/plugin_store/install') }}',
			data: {_token: LA.token,param:data,step:step},
			success: function (data) {
				if (typeof data === 'object') {
					$('.output-box').removeClass('hide');
					$('.output-box .output-body').html(data.data);
				}
				console.log(data);
				// if(data.code == 0){
					if(data.data.finish){
						$('.output-footer').hide();
						commandDialogMsgsPush(data.data.output);
						//$('.output-main').append(htmlss);
						clearInterval(intervalId);
					}else{
						commandDialogMsgsPush(data.data.output);
						console.log('打印输出12');
						console.log(data.data);
						if (data.data && data.data.payWatchUrl) {
							doFinish();
							$('.output-footer').hide();
							clearInterval(intervalId);
							return '';
						}
						setTimeout(() => {
							doCommand(data.data.command, data.data.data, data.data.step);
						}, 1000)
					}


				//}
				//NProgress.done();
			}
		});
	}
	$(function () {
		//NProgress.start();
		doCommand('','{{$paramjson}}','')
	});
</script>
