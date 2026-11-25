<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK - {{ $spk->number }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 25mm 18mm 20mm 18mm;
        }

        body {
            font-family: "DejaVu Serif", serif;
            font-size: 10px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            border: none;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
        }

        .document-info {
            text-align: right;
        }

        .section {
            margin-top: 15px;
        }

        .info-table td {
            border: none;
            padding: 2px 0;
            vertical-align: top;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #888;
            padding: 5px;
            vertical-align: top;
        }

        .items-table th {
            text-align: left;
            font-weight: normal;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .no-border td {
            border: none;
        }

        .approval-table td {
            padding: 7px;
        }

        .totals-table td {
            padding: 3px;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td width="55%">
                <div class="company-name">PT. Decorindo Utama Production</div>
                Gedung Bintaro Business Centre<br>
                Jl. RC Veteran Raya No.01-R1 T.001 RW.003<br>
                Bintaro, Pesanggrahan<br>
                Kota Adm. Jakarta Selatan, DKI Jakarta<br>
                NPWP: 0935 8736 1206 6000<br>
                Email: info@decorindo.com
            </td>

            <td width="45%" class="document-info">
                <strong>Quotation Approval (SPK)</strong><br>
                Rev - 1 / {{ $spk->number }}<br>
                Description : {{ $spk->tanggal->format('d F Y') }}
            </td>
        </tr>
    </table>

    {{-- CLIENT & PROJECT --}}
    <div class="section">
        <table class="info-table">
            <tr>
                <td width="50%">
                    To :<br>
                    {{ $spk->client->nama ?? '-' }}
                </td>

                <td width="50%">
                    Project :<br>
                    {{ $spk->project->nama ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- VENDOR --}}
    <div class="section">
        {{ $spk->vendor->nama ?? '-' }}<br>
        {{ $spk->vendor->alamat ?? '' }}
    </div>

    {{-- ITEMS --}}
    <div class="section">
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="15%">Product</th>
                    <th width="25%">Description</th>
                    <th width="15%">Quotation Number</th>
                    <th width="20%">Project</th>
                    <th width="10%">Price</th>
                    <th width="10%">Total Price</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($spk->items as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $item->item }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quotation_number ?: '-' }}</td>
                        <td>{{ $item->project ?: '-' }}</td>
                        <td>Rp. {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>Rp. {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

        {{-- NOTE & TOTAL --}}
    <div class="section">
        <table class="no-border">
            <tr>
                <td width="65%" style="vertical-align: top;">
                    <strong>Note :</strong><br><br>

                    {!! nl2br(e($spk->note ?: '1. Sebagai penegasan order, harap supaya satu helai tembusan ini dilampirkan pada saat penagihan invoice.
2. Tagihan terdiri dari satu invoice asli disertai dengan faktur pajak.
3. Faktur pajak adalah atas nama PT. Decorindo Utama Production dengan alamat Gedung Bintaro Business Centre, Jl. RC Veteran Raya No.01-R1 T.001 RW.003, Bintaro, Pesanggrahan, Kota Adm. Jakarta Selatan, DKI Jakarta dengan NPWP 0935 8736 1206 6000.
4. Pembayaran akan dilakukan 30 hari setelah invoice diterima lengkap.')) !!}
                </td>

                <td width="35%" style="vertical-align: top;">
                    <table class="no-border">
                        <tr>
                            <td>SubTotal</td>
                            <td class="text-right">
                                Rp. {{ number_format($spk->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>

                        @if ($spk->discount > 0)
                        <tr>
                            <td>Discount</td>
                            <td class="text-right">
                                Rp. {{ number_format($spk->discount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        <tr>
                            <td>Tax</td>
                            <td class="text-right">
                                Rp. {{ number_format($spk->tax_amount, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr>
                            <td><strong>GrandTotal</strong></td>
                            <td class="text-right">
                                <strong>Rp. {{ number_format($spk->grand_total, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- APPROVAL --}}
    <div class="section" style="margin-top: 25px;">
        <table class="items-table approval-table">
            <tr>
                <td width="33.33%" class="text-center">
                    Approval I
                </td>
                <td width="33.33%" class="text-center">
                    Approval II
                </td>
                <td width="33.33%" class="text-center">
                    Approval III
                </td>
            </tr>

            <tr>
                <td style="height: 75px;"></td>
                <td style="height: 75px;"></td>
                <td style="height: 75px;"></td>
            </tr>

            <tr>
                <td class="text-center">
                    Name : {{ $spk->ttd1_name ?: '-' }}<br>
                    {{ $spk->ttd1_title ?: '' }}
                </td>

                <td class="text-center">
                    Name : {{ $spk->ttd2_name ?: '-' }}<br>
                    {{ $spk->ttd2_title ?: '' }}
                </td>

                <td class="text-center">
                    Name : {{ $spk->ttd3_name ?: '-' }}<br>
                    {{ $spk->ttd3_title ?: '' }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>