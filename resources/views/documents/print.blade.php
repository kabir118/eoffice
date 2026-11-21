<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - {{ $document->file_number }} - USTB e-Office</title>
    <style>
        /* ===== Reset & Base ===== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            line-height: 1.5;
            background: #fff;
            padding: 20px 30px;
        }

        /* ===== Official University Letterhead ===== */
        .official-letterhead {
            width: 100%;
            margin-bottom: 20px;
        }
        .letterhead-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        .letterhead-logo img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        .letterhead-text {
            text-align: center;
        }
        .university-name {
            font-family: 'Times New Roman', Georgia, serif;
            font-style: italic;
            font-size: 24px;
            font-weight: 700;
            color: #000;
            margin-bottom: 3px;
            line-height: 1.2;
        }
        .university-address {
            font-size: 13px;
            font-weight: 600;
            color: #111;
            margin-bottom: 2px;
        }
        .university-contact {
            font-size: 11.5px;
            color: #222;
        }
        .double-line {
            border-bottom: 3px double #000;
            margin-top: 8px;
            margin-bottom: 12px;
        }
        .ref-date-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: 600;
            color: #000;
            font-family: 'Times New Roman', Georgia, serif;
        }

        .print-title {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            color: #013756;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ===== Meta Table ===== */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .meta-table td {
            padding: 6px 10px;
            border: 1px solid #ccc;
            font-size: 11px;
            vertical-align: top;
        }
        .meta-table td.label {
            font-weight: 700;
            color: #013756;
            width: 140px;
            background-color: #f4f7fa;
        }

        /* ===== Description ===== */
        .description-box {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 20px;
            background-color: #fafbfc;
        }
        .description-box h3 {
            font-size: 12px;
            font-weight: 700;
            color: #013756;
            margin-bottom: 6px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 4px;
        }
        .description-box p {
            font-size: 11px;
            white-space: pre-line;
            color: #333;
        }

        /* ===== Section Title ===== */
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #013756;
            border-bottom: 2px solid #013756;
            padding-bottom: 4px;
            margin-bottom: 12px;
            margin-top: 24px;
        }

        /* ===== Comments Table ===== */
        .comments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .comments-table th {
            background-color: #013756;
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            padding: 6px 8px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .comments-table td {
            padding: 7px 8px;
            border: 1px solid #d0d0d0;
            font-size: 11px;
            vertical-align: top;
        }
        .comments-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .comments-table .comment-user {
            font-weight: 600;
            color: #013756;
        }
        .comments-table .comment-designation {
            font-size: 9px;
            color: #777;
            display: block;
        }
        .comments-table .comment-date {
            font-size: 10px;
            color: #666;
            white-space: nowrap;
        }

        /* ===== Print Media ===== */
        @media print {
            body {
                padding: 0;
                margin: 0;
                font-size: 11px;
            }
            .print-controls {
                display: none !important;
            }
            @page {
                margin: 12mm 15mm;
                size: A4 portrait;
            }
        }
    </style>
</head>
<body>

    <!-- Print/Back Controls -->
    <div class="print-controls">
        <button class="btn-print" onclick="window.print()">
            🖨️ Print Document
        </button>
        <a class="btn-back" href="{{ route('documents.show', $document->id) }}">
            ← Back to File
        </a>
    </div>

    <!-- Official University Letterhead -->
    <div class="official-letterhead">
        <div class="letterhead-header">
            <div class="letterhead-logo">
                <img src="https://ustb.edu.pk/assets/img/logo.png" alt="UST Bannu Crest Logo">
            </div>
            <div class="letterhead-text">
                <h1 class="university-name">University of Science &amp; Technology, Bannu</h1>
                <p class="university-address">Bannu Township, Bannu Khyber Pakhtunkhwa (Pakistan)</p>
                <p class="university-contact">Ph # 0928-633823, Fax # 0928-633821, http://www.ustb.edu.pk</p>
            </div>
        </div>
        <div class="double-line"></div>
        <div class="ref-date-row">
            <div class="ref-no"><em>No. UST/</em>{{ $document->file_number }}</div>
            <div class="doc-date"><em>Dated:</em> {{ $document->created_at->format('F d, Y') }}</div>
        </div>
    </div>

    <div class="print-title">File Tracking Sheet</div>

    <div class="print-date">
        Printed on: {{ now()->format('F d, Y - h:i A') }}
    </div>

    <!-- Document Metadata -->
    <table class="meta-table">
        <tr>
            <td class="label">File / Tracking No.</td>
            <td>{{ $document->file_number }}</td>
            <td class="label">Priority</td>
            <td>{{ ucfirst(str_replace('_', ' ', $document->priority)) }}</td>
        </tr>
        <tr>
            <td class="label">Subject</td>
            <td colspan="3">{{ $document->title }}</td>
        </tr>
        <tr>
            <td class="label">Current Status</td>
            <td>{{ ucfirst($document->status) }}</td>
            <td class="label">Initiated By</td>
            <td>{{ $document->creator->name }}{{ $document->creator->designation ? ' (' . $document->creator->designation->title . ')' : '' }}</td>
        </tr>
        <tr>
            <td class="label">Current Holder</td>
            <td>{{ $document->currentHolder->name }}{{ $document->currentHolder->designation ? ' (' . $document->currentHolder->designation->title . ')' : '' }}</td>
            <td class="label">Department</td>
            <td>{{ $document->currentDepartment ? $document->currentDepartment->name : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Initiation Date</td>
            <td>{{ $document->created_at->format('M d, Y - h:i A') }}</td>
            <td class="label">Last Updated</td>
            <td>{{ $document->updated_at->format('M d, Y - h:i A') }}</td>
        </tr>
        @if($document->attachment_path)
        <tr>
            <td class="label">Attachment</td>
            <td colspan="3">{{ basename($document->attachment_path) }}</td>
        </tr>
        @endif
    </table>

    @if($document->urgency_remarks && in_array($document->priority, ['urgent', 'most_urgent']))
    <div class="description-box" style="border-left: 3px solid #e74c3c;">
        <h3>⚠ Urgency Justification</h3>
        <p>{{ $document->urgency_remarks }}</p>
    </div>
    @endif

    <!-- File Description / Summary -->
    <div class="description-box">
        <h3>File Summary / Note</h3>
        <p>{{ $document->description }}</p>
    </div>

    <!-- All Comments & Remarks -->
    <h2 class="section-title">📋 Comments & Remarks ({{ $unifiedComments->count() }})</h2>

    @if($unifiedComments->count() > 0)
        <table class="comments-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 18%;">Author</th>
                    <th style="width: 12%;">Type</th>
                    <th style="width: 45%;">Comment / Remarks</th>
                    <th style="width: 10%;">Attachment</th>
                    <th style="width: 10%;">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unifiedComments as $index => $comment)
                    @php
                        $isVc = false;
                        if ($comment->user) {
                            $desig = strtolower(trim($comment->user->designation->title ?? ''));
                            $uName = strtolower(trim($comment->user->name ?? ''));
                            
                            $hasVcTitle = ($desig === 'vice chancellor' || $desig === 'vice-chancellor' || $desig === 'vc' || str_starts_with($desig, 'vice chancellor') || str_starts_with($desig, 'vice-chancellor') || $uName === 'vice chancellor' || $uName === 'vc');
                            $isAssistantOrStaff = (str_contains($desig, 'p.s') || str_contains($desig, 'ps') || str_contains($desig, 'p.a') || str_contains($desig, 'pa ') || str_contains($desig, 'secretary') || str_contains($desig, 'assistant') || str_contains($desig, 'clerk') || str_contains($desig, 'officer'));

                            if ($hasVcTitle && !$isAssistantOrStaff) {
                                $isVc = true;
                            }
                        }
                    @endphp
                    <tr style="{{ $isVc ? 'background-color: #eafaf1; border-left: 4px solid #27ae60;' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span class="comment-user" style="{{ $isVc ? 'color: #27ae60; font-weight: 700;' : '' }}">
                                {{ $comment->user->name }}
                                @if($isVc)
                                    <span style="display: block; font-size: 8px; color: #27ae60; font-weight: 700;">(VC Remarks)</span>
                                @endif
                            </span>
                            <span class="comment-designation">
                                {{ $comment->user->designation ? $comment->user->designation->title : ($comment->user->department ? $comment->user->department->name : '') }}
                            </span>
                        </td>
                        <td>
                            @if($isVc)
                                <span class="badge" style="background-color: #27ae60; color: #fff; font-weight: 700;">VC Remarks</span>
                            @elseif($comment->type === 'track_remarks')
                                <span class="badge badge-{{ $comment->status ?? 'forwarded' }}">{{ ucfirst($comment->status ?? 'Forwarded') }}</span>
                            @else
                                <span class="badge badge-comment">Comment</span>
                            @endif
                        </td>
                        <td style="{{ $isVc ? 'color: #1e8449; font-weight: 600;' : '' }}">{{ $comment->content }}</td>
                        <td>
                            @if($comment->attachment_path)
                                <span class="attachment-note">📎 {{ basename($comment->attachment_path) }}</span>
                            @else
                                <span style="color: #ccc;">—</span>
                            @endif
                        </td>
                        <td class="comment-date">{{ $comment->created_at->format('M d, Y') }}<br>{{ $comment->created_at->format('h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #999; padding: 15px 0; font-style: italic;">No comments or remarks recorded on this file.</p>
    @endif

    <!-- Routing History -->
    <h2 class="section-title">🔄 Routing History ({{ $tracks->count() }} Transfers)</h2>

    @if($tracks->count() > 0)
        <table class="routing-table">
            <thead>
                <tr>
                    <th style="width: 6%;">#</th>
                    <th style="width: 25%;">From</th>
                    <th style="width: 25%;">To</th>
                    <th style="width: 12%;">Action</th>
                    <th style="width: 14%;">Attachment</th>
                    <th style="width: 18%;">Date / Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tracks as $index => $track)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $track->sender->name }}</strong>
                            <span class="comment-designation">
                                {{ $track->sender->designation ? $track->sender->designation->title : ($track->sender->department ? $track->sender->department->name : '') }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $track->receiver->name ?? 'N/A' }}</strong>
                            <span class="comment-designation">
                                {{ $track->receiver && $track->receiver->designation ? $track->receiver->designation->title : ($track->receiver && $track->receiver->department ? $track->receiver->department->name : '') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $track->status_at_transfer }}">{{ ucfirst($track->status_at_transfer) }}</span>
                        </td>
                        <td>
                            @if($track->attachment_path)
                                <span class="attachment-note">📎 {{ basename($track->attachment_path) }}</span>
                            @else
                                <span style="color: #ccc;">—</span>
                            @endif
                        </td>
                        <td class="comment-date">{{ $track->created_at->format('M d, Y') }}<br>{{ $track->created_at->format('h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #999; padding: 15px 0; font-style: italic;">No routing history recorded.</p>
    @endif

    <!-- Signature Block -->
    <div style="margin-top: 40px; page-break-inside: avoid;">
        <h2 class="section-title">✍ Signatures</h2>
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <td style="width: 50%; padding: 15px 20px; vertical-align: top;">
                    <div style="margin-bottom: 50px;">
                        <p style="font-size: 10px; color: #777; margin-bottom: 4px;">Prepared / Forwarded By:</p>
                        <p style="font-size: 11px; font-weight: 700; color: #013756;">
                            {{ $document->creator->name }}
                            @if($document->creator->designation)
                                <br><span style="font-weight: 400; font-size: 10px; color: #555;">{{ $document->creator->designation->title }}</span>
                            @endif
                        </p>
                    </div>
                    <div style="border-bottom: 1px solid #333; width: 200px; margin-bottom: 4px;"></div>
                    <p style="font-size: 9px; color: #777;">Signature &amp; Date</p>
                </td>
                <td style="width: 50%; padding: 15px 20px; vertical-align: top;">
                    <div style="margin-bottom: 50px;">
                        <p style="font-size: 10px; color: #777; margin-bottom: 4px;">Current Holder / Approving Authority:</p>
                        <p style="font-size: 11px; font-weight: 700; color: #013756;">
                            {{ $document->currentHolder->name }}
                            @if($document->currentHolder->designation)
                                <br><span style="font-weight: 400; font-size: 10px; color: #555;">{{ $document->currentHolder->designation->title }}</span>
                            @endif
                            @if($document->currentDepartment)
                                <br><span style="font-weight: 400; font-size: 10px; color: #555;">{{ $document->currentDepartment->name }}</span>
                            @endif
                        </p>
                    </div>
                    <div style="border-bottom: 1px solid #333; width: 200px; margin-bottom: 4px;"></div>
                    <p style="font-size: 9px; color: #777;">Signature &amp; Date</p>
                </td>
            </tr>
        </table>

        {{-- Additional signature lines for the last 2 unique routing holders (excluding creator and current holder) --}}
        @php
            $signatories = $tracks->map(function($t) { return $t->receiver; })
                ->filter()
                ->unique('id')
                ->reject(function($u) use ($document) {
                    return $u->id === $document->creator_id || $u->id === $document->current_holder_id;
                })
                ->take(2);
        @endphp

        @if($signatories->count() > 0)
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <tr>
                    @foreach($signatories as $signatory)
                        <td style="width: 50%; padding: 15px 20px; vertical-align: top;">
                            <div style="margin-bottom: 50px;">
                                <p style="font-size: 10px; color: #777; margin-bottom: 4px;">Reviewed / Endorsed By:</p>
                                <p style="font-size: 11px; font-weight: 700; color: #013756;">
                                    {{ $signatory->name }}
                                    @if($signatory->designation)
                                        <br><span style="font-weight: 400; font-size: 10px; color: #555;">{{ $signatory->designation->title }}</span>
                                    @endif
                                </p>
                            </div>
                            <div style="border-bottom: 1px solid #333; width: 200px; margin-bottom: 4px;"></div>
                            <p style="font-size: 9px; color: #777;">Signature &amp; Date</p>
                        </td>
                    @endforeach
                    @if($signatories->count() === 1)
                        <td style="width: 50%;"></td>
                    @endif
                </tr>
            </table>
        @endif
    </div>

    <!-- Footer -->
    <div class="print-footer">
        <p>University of Science &amp; Technology, Bannu &mdash; e-Office File Tracking System</p>
        <p>This is a computer-generated document. File Ref: {{ $document->file_number }} | Generated: {{ now()->format('M d, Y h:i A') }}</p>
    </div>

</body>
</html>

