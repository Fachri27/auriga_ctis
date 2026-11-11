{{-- Grafik echarts untuk statistik perkara (elemen .pub-echart dengan data-chart & data-type).
     Dipakai di Beranda dan mode Perkara dashboard. window.pubInitCharts(root) / pubDisposeCharts(root) dipakai
     ulang setelah konten ditukar tanpa reload; echarts baru dimuat saat ada grafik yang perlu digambar.
     $autoInit (bawaan true): gambar semua grafik di halaman saat dimuat. --}}
<script>
    (function () {
        // Validated categorical palette (dataviz skill, green-first order)
        var CAT = ['#008300', '#2a78d6', '#eda100', '#e87ba4', '#1baf7a', '#eb6834', '#4a3aa7', '#e34948'];
        var GREEN = '#008300';
        var INK = '#374151', MUTED = '#6b7268', GRID = '#EEF1E8', AXISLINE = '#E2E6DA';
        var FONT = "'IBM Plex Mono', monospace";
        // ponytail: console-style bar fill — deep-green base fading to bright leaf at the data end
        function barFill(vertical) {
            return {
                type: 'linear',
                x: 0, y: 0, x2: vertical ? 0 : 1, y2: vertical ? 1 : 0,
                colorStops: [
                    { offset: 0, color: vertical ? '#3FA90F' : '#1F6300' },
                    { offset: 1, color: vertical ? '#1F6300' : '#3FA90F' }
                ]
            };
        }

        window.pubChartInstances = window.pubChartInstances || {};

        function axisCommon(labels, long) {
            return {
                type: 'category',
                data: labels,
                axisLine: { lineStyle: { color: AXISLINE } },
                axisTick: { show: false },
                axisLabel: {
                    color: MUTED, fontSize: 10, fontFamily: FONT,
                    interval: long ? Math.ceil(labels.length / 20) : 0,
                    rotate: long ? 55 : 0
                }
            };
        }

        function valueAxis() {
            return {
                type: 'value', minInterval: 1,
                axisLabel: { color: MUTED, fontSize: 10, fontFamily: FONT },
                splitLine: { lineStyle: { color: GRID } }
            };
        }

        function tooltipFmt(params) {
            var p = Array.isArray(params) ? params[0] : params;
            return '<b>' + p.name + '</b><br/>' + Number(p.value).toLocaleString('id-ID');
        }

        function pubInitChart(el) {
            var raw = el.getAttribute('data-chart');
            if (!raw) return;
            var data = JSON.parse(raw);
            if (!data.length) return;

            if (window.pubChartInstances[el.id]) window.pubChartInstances[el.id].dispose();
            var chart = echarts.init(el);
            window.pubChartInstances[el.id] = chart;

            var type = el.getAttribute('data-type') || 'bar';
            var labels = data.map(function (d) { return d.label; });
            var values = data.map(function (d) { return d.value; });
            var long = labels.length > 15;

            if (type === 'pie') {
                chart.setOption({
                    textStyle: { fontFamily: FONT },
                    tooltip: {
                        trigger: 'item',
                        formatter: function (p) {
                            return '<b>' + p.name + '</b><br/>' + p.value.toLocaleString('id-ID') + ' (' + p.percent.toFixed(1) + '%)';
                        }
                    },
                    series: [{
                        type: 'pie',
                        radius: ['38%', '68%'],
                        data: data.map(function (d) { return { name: d.label, value: d.value }; }),
                        itemStyle: {
                            color: function (p) { return CAT[p.dataIndex % CAT.length]; },
                            borderColor: '#fff', borderWidth: 2, borderRadius: 3
                        },
                        label: {
                            color: INK, fontSize: 10, fontFamily: FONT,
                            formatter: function (p) { return p.name + '\n' + p.value.toLocaleString('id-ID'); }
                        },
                        labelLine: { lineStyle: { color: AXISLINE } }
                    }]
                });
            } else if (type === 'line') {
                chart.setOption({
                    textStyle: { fontFamily: FONT },
                    tooltip: { trigger: 'axis', axisPointer: { type: 'line' }, formatter: tooltipFmt },
                    grid: { left: 8, right: 16, top: 20, bottom: long ? 60 : 10, containLabel: true },
                    xAxis: axisCommon(labels, long),
                    yAxis: valueAxis(),
                    series: [{
                        type: 'line',
                        data: values,
                        lineStyle: { width: 2, color: GREEN },
                        itemStyle: { color: GREEN, borderColor: '#fff', borderWidth: 2 },
                        symbol: 'circle', symbolSize: 8,
                        areaStyle: {
                            color: {
                                type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
                                colorStops: [{ offset: 0, color: 'rgba(0,131,0,0.16)' }, { offset: 1, color: 'rgba(0,131,0,0.01)' }]
                            }
                        }
                    }]
                });
            } else if (type === 'hbar') {
                chart.setOption({
                    textStyle: { fontFamily: FONT },
                    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, formatter: tooltipFmt },
                    grid: { left: 8, right: 40, top: 8, bottom: 8, containLabel: true },
                    xAxis: valueAxis(),
                    yAxis: {
                        type: 'category',
                        data: labels.slice().reverse(),
                        axisLine: { lineStyle: { color: AXISLINE } },
                        axisTick: { show: false },
                        axisLabel: { color: MUTED, fontSize: 9, fontFamily: FONT, width: 120, overflow: 'truncate' }
                    },
                    series: [{
                        type: 'bar',
                        data: values.slice().reverse(),
                        barMaxWidth: 16,
                        itemStyle: { color: barFill(false), borderRadius: 0 },
                        emphasis: { itemStyle: { color: '#0B1E07' } }
                    }]
                });
            } else {
                chart.setOption({
                    textStyle: { fontFamily: FONT },
                    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, formatter: tooltipFmt },
                    grid: { left: 8, right: 16, top: 20, bottom: long ? 60 : 10, containLabel: true },
                    xAxis: axisCommon(labels, long),
                    yAxis: valueAxis(),
                    dataZoom: long ? [{ type: 'slider', start: 0, end: 30, height: 16, bottom: 5 }] : undefined,
                    series: [{
                        type: 'bar',
                        data: values,
                        barMaxWidth: 28,
                        itemStyle: { color: barFill(true), borderRadius: 0 },
                        emphasis: { itemStyle: { color: '#0B1E07' } }
                    }]
                });
            }
        }

        var echartsLoading = null;
        function loadEcharts() {
            if (window.echarts) return Promise.resolve();
            echartsLoading = echartsLoading || new Promise(function (resolve, reject) {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/echarts@6/dist/echarts.min.js';
                script.onload = resolve;
                script.onerror = function () { echartsLoading = null; reject(new Error('echarts gagal dimuat')); };
                document.head.appendChild(script);
            });
            return echartsLoading;
        }

        window.pubInitCharts = function (root) {
            var els = (root || document).querySelectorAll('.pub-echart');
            if (!els.length) return Promise.resolve();
            return loadEcharts().then(function () { els.forEach(pubInitChart); });
        };

        window.pubDisposeCharts = function (root) {
            (root || document).querySelectorAll('.pub-echart').forEach(function (el) {
                if (window.pubChartInstances[el.id]) {
                    window.pubChartInstances[el.id].dispose();
                    delete window.pubChartInstances[el.id];
                }
            });
        };

        @if ($autoInit ?? true)
        window.pubInitCharts(document);
        @endif

        function pubResizeAll() {
            Object.keys(window.pubChartInstances).forEach(function (id) {
                if (window.pubChartInstances[id]) window.pubChartInstances[id].resize();
            });
        }
        window.pubResizeAll = pubResizeAll;
        window.addEventListener('resize', pubResizeAll);

    })();
</script>
