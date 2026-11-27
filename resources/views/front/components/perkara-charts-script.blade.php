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

        function valueAxis(fmt) {
            return {
                type: 'value', minInterval: 1,
                axisLabel: { color: MUTED, fontSize: 10, fontFamily: FONT, formatter: fmt ? function (v) { return fmt(v, true); } : undefined },
                splitLine: { lineStyle: { color: GRID } }
            };
        }

        // Format nilai per grafik. data-unit="rupiah": diringkas di sumbu (Rp500 jt); data-unit-label: satuan di tooltip (mis. bulan).
        function valueFormatter(el) {
            var unit = el.getAttribute('data-unit'), label = el.getAttribute('data-unit-label');
            var locale = document.documentElement.lang === 'en' ? 'en-US' : 'id-ID';
            return function (v, compact) {
                if (unit === 'rupiah') {
                    return 'Rp' + Number(v).toLocaleString(locale, compact ? { notation: 'compact', maximumFractionDigits: 1 } : { maximumFractionDigits: 0 });
                }
                return Number(v).toLocaleString(locale, { maximumFractionDigits: 1 }) + (unit && label && !compact ? ' ' + label : '');
            };
        }

        function tooltipFmt(fmt) {
            return function (params) {
                var p = Array.isArray(params) ? params[0] : params;
                return '<b>' + p.name + '</b><br/>' + fmt(p.value, false);
            };
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
            var fmt = valueFormatter(el);
            var unitAxis = el.getAttribute('data-unit') ? fmt : null;
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
                    tooltip: { trigger: 'axis', axisPointer: { type: 'line' }, formatter: tooltipFmt(fmt) },
                    grid: { left: 8, right: 16, top: 20, bottom: long ? 60 : 10, containLabel: true },
                    xAxis: axisCommon(labels, long),
                    yAxis: valueAxis(unitAxis),
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
                    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, formatter: tooltipFmt(fmt) },
                    grid: { left: 8, right: 40, top: 8, bottom: 8, containLabel: true },
                    xAxis: valueAxis(unitAxis),
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
            } else if (el.getAttribute('data-series')) {
                // Beberapa batang per label (mis. tuntutan & vonis per tahun): warna menurut slot palet kategori,
                // legenda di atas, tooltip berisi semua seri plus vonis sebagai persen tuntutan.
                var series = JSON.parse(el.getAttribute('data-series'));
                var ratioLabel = el.getAttribute('data-ratio-label');
                var keys = series.map(function (s) { return s.key; });
                chart.setOption({
                    textStyle: { fontFamily: FONT },
                    legend: {
                        show: series.length > 1, top: 0, left: 0, icon: 'roundRect', itemWidth: 10, itemHeight: 10, itemGap: 16,
                        textStyle: { color: INK, fontSize: 11, fontFamily: FONT }
                    },
                    tooltip: {
                        trigger: 'axis', axisPointer: { type: 'shadow' },
                        formatter: function (params) {
                            var lines = ['<b>' + params[0].name + '</b>'];
                            params.forEach(function (p) {
                                lines.push(p.marker + p.seriesName + ': ' + (p.value == null ? '–' : fmt(p.value, false)));
                            });
                            var row = data[params[0].dataIndex];
                            if (ratioLabel && keys.indexOf('tuntutan') >= 0 && keys.indexOf('vonis') >= 0 && row.tuntutan > 0 && row.vonis != null) {
                                var pct = (row.vonis / row.tuntutan * 100).toLocaleString(document.documentElement.lang === 'en' ? 'en-US' : 'id-ID', { maximumFractionDigits: 0 }) + '%';
                                lines.push('<span style="color:' + MUTED + '">' + ratioLabel.replace(':percent', pct) + '</span>');
                            }
                            return lines.join('<br/>');
                        }
                    },
                    grid: { left: 8, right: 16, top: 36, bottom: 10, containLabel: true },
                    xAxis: axisCommon(labels, long),
                    yAxis: valueAxis(unitAxis),
                    series: series.map(function (s) {
                        return {
                            type: 'bar',
                            name: s.name,
                            data: data.map(function (d) { return d[s.key]; }),
                            barMaxWidth: 18, barGap: '12%',
                            itemStyle: { color: CAT[s.slot], borderRadius: [4, 4, 0, 0] },
                            emphasis: { focus: 'series' }
                        };
                    })
                });
            } else {
                chart.setOption({
                    textStyle: { fontFamily: FONT },
                    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, formatter: tooltipFmt(fmt) },
                    grid: { left: 8, right: 16, top: 20, bottom: long ? 60 : 10, containLabel: true },
                    xAxis: axisCommon(labels, long),
                    yAxis: valueAxis(unitAxis),
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
