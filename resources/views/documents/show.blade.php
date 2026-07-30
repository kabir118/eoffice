@extends('layouts.app')

@section('title', 'File Tracking - USTB e-Office')
@section('page_title')
    Tracking Details: {{ $document->file_number }}
@endsection

@section('content')
    <div style="display: flex; flex-direction: column; gap: 25px; max-width: 100%;">

        {{-- Document Details Card --}}
        <div class="dashboard-card" style="margin-bottom: 0;">
            <div class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                <span>Document Information</span>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button onclick="toggleTrackingTrail()" class="btn btn-secondary btn-sm" id="btn-tracking-toggle" style="display: inline-flex; align-items: center; gap: 5px;">
                        <i class="bi bi-signpost-2-fill"></i>
                        <span>Show Tracking Trail</span>
                    </button>
                    <a href="{{ route('documents.print', $document->id) }}" target="_blank" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 5px;">
                        <i class="bi bi-printer-fill"></i>
                        <span>Print</span>
                    </a>
                    @if(Auth::user()->isSuperAdmin())
                        @if($document->trashed())
                            <form action="{{ route('documents.restore', $document->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm" style="color: #27ae60; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    <span>Restore File</span>
                                </button>
                            </form>
                            <form action="{{ route('documents.forceDelete', $document->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('PERMANENTLY delete file {{ $document->file_number }}? This CANNOT be undone!');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-sm" style="color: #e74c3c; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="bi bi-trash3-fill"></i>
                                    <span>Delete Permanently</span>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('documents.destroy', $document->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Move file {{ $document->file_number }} to Trash Bin?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-sm" style="color: #e74c3c; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="bi bi-trash-fill"></i>
                                    <span>Move to Trash</span>
                                </button>
                            </form>
                        @endif
                    @endif
                    <a href="{{ route('documents.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back</span>
                    </a>
                </div>
            </div>

            @if($document->trashed())
                <div style="background-color: rgba(231,76,60,0.08); border-left: 5px solid #e74c3c; padding: 15px 20px; border-radius: var(--border-radius); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <strong style="color: #e74c3c; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                            <i class="bi bi-trash-fill"></i> File Currently in Trash Bin
                        </strong>
                        <p style="font-size: 0.83rem; color: var(--text-main); margin: 4px 0 0 0;">This file was moved to the Recycle Bin. You can restore it to active workspace or delete it permanently.</p>
                    </div>
                </div>
            @endif

            @if($document->urgency_remarks && in_array($document->priority, ['urgent', 'most_urgent']))
                <div style="background-color: rgba(231,76,60,0.04); border-left: 4px solid var(--priority-urgent); padding: 15px 20px; border-radius: var(--border-radius); margin-bottom: 20px;">
                    <strong style="color: var(--priority-urgent); display: flex; align-items: center; gap: 8px; font-size: 0.92rem; margin-bottom: 5px;">
                        <i class="bi bi-exclamation-octagon-fill"></i> Urgency Justification ({{ ucfirst(str_replace('_', ' ', $document->priority)) }})
                    </strong>
                    <p style="font-size: 0.85rem; color: var(--text-main); margin: 0; white-space: pre-line;">{{ $document->urgency_remarks }}</p>
                </div>
            @endif

            {{-- File Subject Banner --}}
            <div style="background: linear-gradient(135deg, #013756 0%, #256f88 100%); border-radius: var(--border-radius); padding: 16px 22px; margin-bottom: 20px; color: #fff;">
                <span style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; display: block; margin-bottom: 3px;">📄 File Subject</span>
                <span style="font-size: 1.15rem; font-weight: 700; line-height: 1.4; display: block;">{{ $document->title }}</span>
            </div>

            {{-- Clean Metadata Table --}}
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid var(--border-color); border-radius: var(--border-radius); overflow: hidden;">
                <tr>
                    <td style="padding: 10px 16px; background-color: #f6f8fa; font-size: 0.75rem; font-weight: 700; color: var(--primary-dark); width: 140px; border-bottom: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">Tracking ID</td>
                    <td style="padding: 10px 16px; font-size: 0.88rem; font-weight: 600; color: var(--primary-color); border-bottom: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">{{ $document->file_number }}</td>
                    <td style="padding: 10px 16px; background-color: #f6f8fa; font-size: 0.75rem; font-weight: 700; color: var(--primary-dark); width: 140px; border-bottom: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">Priority</td>
                    <td style="padding: 10px 16px; border-bottom: 1px solid var(--border-color);">
                        @if($document->priority === 'most_urgent')
                            <span style="font-weight: 800; color: #e74c3c; font-size: 0.88rem;">⚠ Most Urgent</span>
                        @else
                            <span class="badge badge-priority-{{ $document->priority }}">{{ ucfirst(str_replace('_', ' ', $document->priority)) }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 16px; background-color: #f6f8fa; font-size: 0.75rem; font-weight: 700; color: var(--primary-dark); border-bottom: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">Status</td>
                    <td style="padding: 10px 16px; border-bottom: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">
                        @if($document->status === 'completed')
                            <span class="badge" style="background-color: #27ae60; color: #fff; font-weight: 700;">Closed</span>
                        @else
                            <span class="badge" style="background-color: #3498db; color: #fff; font-weight: 600;">Open</span>
                        @endif
                        @if(!$document->is_available_to_registrar)
                            <span class="badge" style="background-color: #8e44ad; color: #ffffff; font-weight: 700; margin-left: 6px;" title="Restricted from Registrar Office general access">
                                <i class="bi bi-shield-lock-fill"></i> Restricted from Registrar
                            </span>
                        @endif
                    </td>
                    <td style="padding: 10px 16px; background-color: #f6f8fa; font-size: 0.75rem; font-weight: 700; color: var(--primary-dark); border-bottom: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">Initiated By</td>
                    <td style="padding: 10px 16px; font-size: 0.85rem; border-bottom: 1px solid var(--border-color);">
                        <strong style="color: var(--primary-dark); display: block;">{{ $document->creator ? $document->creator->name : 'N/A' }}</strong>
                        @if($document->creator && $document->creator->designation)
                            <span style="font-size: 0.76rem; color: var(--text-muted); display: block; margin-top: 2px;">{{ $document->creator->designation->title }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 16px; background-color: #f6f8fa; font-size: 0.75rem; font-weight: 700; color: var(--primary-dark); border-bottom: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">Created On</td>
                    <td style="padding: 10px 16px; font-size: 0.85rem; color: #2c3e50; font-weight: 600; border-bottom: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">{{ $document->created_at->format('M d, Y — h:i A') }}</td>
                    <td style="padding: 10px 16px; background-color: #f6f8fa; font-size: 0.75rem; font-weight: 700; color: var(--primary-dark); border-bottom: 1px solid var(--border-color); border-right: 1px solid var(--border-color);">Currently With</td>
                    <td style="padding: 10px 16px; font-size: 0.85rem; border-bottom: 1px solid var(--border-color);">
                        <strong style="color: var(--primary-dark);">{{ $document->currentHolder->name }}</strong>
                        @if($document->currentHolder->designation)
                            <span style="font-size: 0.75rem; color: var(--text-muted);"> — {{ $document->currentHolder->designation->title }}</span>
                        @endif
                    </td>
                </tr>
                @if($document->currentDepartment)
                <tr>
                    <td style="padding: 10px 16px; background-color: #f6f8fa; font-size: 0.75rem; font-weight: 700; color: var(--primary-dark); border-right: 1px solid var(--border-color);">Department</td>
                    <td colspan="3" style="padding: 10px 16px; font-size: 0.85rem;">{{ $document->currentDepartment->name }}</td>
                </tr>
                @endif
            </table>

            <!-- File Summary / Note -->
            <div style="background-color: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 18px 20px; margin-bottom: 20px;">
                <strong style="color: var(--primary-dark); display: flex; align-items: center; gap: 6px; font-size: 0.82rem; margin-bottom: 8px;">
                    <i class="bi bi-file-text-fill" style="color: var(--primary-color);"></i> File Summary / Note
                </strong>
                <p style="font-size: 0.9rem; color: var(--text-main); white-space: pre-line; margin: 0; line-height: 1.6;">{{ $document->description }}</p>
            </div>

            <!-- File Attachment Section -->
            @if($document->attachment_path)
                @php 
                    $ext = strtolower(pathinfo($document->attachment_path, PATHINFO_EXTENSION)); 
                    $inlineUrl = route('documents.download', $document->id) . '?inline=true';
                @endphp
                
                <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 20px; background-color: rgba(37,111,136,0.02); margin-bottom: 25px;">
                    <strong style="color: var(--primary-dark); display: block; margin-bottom: 12px;"><i class="bi bi-paperclip"></i> Attachment Preview</strong>
                    
                    <!-- Real-time Preview Area -->
                    <div style="margin-bottom: 15px;">
                        @if($ext === 'pdf')
                            <iframe src="{{ $inlineUrl }}" style="width: 100%; height: 450px; border: 1px solid var(--border-color); border-radius: 8px;"></iframe>
                        @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                            <div style="text-align: center; background-color: #ffffff; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                                <img src="{{ $inlineUrl }}" style="max-width: 100%; max-height: 350px; border-radius: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" alt="Attachment Preview">
                            </div>
                        @else
                            <div style="display: flex; align-items: center; gap: 15px; padding: 15px; background: #ffffff; border-radius: 8px; border: 1px solid var(--border-color);">
                                @if(in_array($ext, ['docx', 'doc']))
                                    <i class="bi bi-file-earmark-word-fill" style="font-size: 2.5rem; color: #2b579a;"></i>
                                @elseif(in_array($ext, ['xlsx', 'xls']))
                                    <i class="bi bi-file-earmark-excel-fill" style="font-size: 2.5rem; color: #217346;"></i>
                                @else
                                    <i class="bi bi-file-earmark-zip-fill" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                                @endif
                                <div>
                                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--primary-dark); display: block;">{{ basename($document->attachment_path) }}</span>
                                    <span style="font-size: 0.72rem; color: var(--text-muted);">Preview not supported for this file type. Click download to view.</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Download button row -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 15px;">
                        <span style="font-size: 0.72rem; color: var(--text-muted);"><i class="bi bi-shield-lock-fill"></i> Secure university file transfer</span>
                        <a href="{{ route('documents.download', $document->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-download"></i>
                            <span>Download Attachment</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Collapsible Tracking Trail --}}
        <div id="tracking-trail-section" class="dashboard-card" style="margin-bottom: 0; display: none;">
            <div class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                <span>Tracking Trail Flow</span>
                <button onclick="toggleTrackingTrail()" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bi bi-x-lg"></i>
                    <span>Close</span>
                </button>
            </div>

            <ul class="timeline">
                <!-- Current Position -->
                @if($document->status !== 'completed')
                    <li class="timeline-item active">
                        <span class="timeline-marker"><i class="bi bi-clock-history" style="font-size: 0.6rem; color: white;"></i></span>
                        <div class="timeline-content" style="background-color: var(--white); border-color: var(--accent-color);">
                            <div class="timeline-header">
                                <span class="timeline-user" style="color: var(--primary-dark);">Currently Held By</span>
                            </div>
                            <div style="font-size: 0.82rem; font-weight: 600; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-person-fill" style="color: var(--accent-color);"></i>
                                <span>
                                    {{ $document->currentHolder->name }}
                                    @if($document->currentHolder->designation)
                                        <span style="font-weight: 400; font-size: 0.72rem; color: var(--text-muted);">({{ $document->currentHolder->designation->title }})</span>
                                    @endif
                                </span>
                            </div>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                Department: {{ $document->currentDepartment ? $document->currentDepartment->name : 'N/A' }}
                            </div>
                        </div>
                    </li>
                @endif

                <!-- History Timeline Items -->
                @foreach($tracks as $index => $track)
                    <li class="timeline-item">
                        <span class="timeline-marker"><i class="bi bi-check-lg" style="font-size: 0.6rem; color: var(--primary-color);"></i></span>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <span class="timeline-user">
                                    {{ $track->sender->name }}
                                    @if($track->sender->designation)
                                        <span style="font-weight: 400; font-size: 0.72rem; color: var(--text-muted);">({{ $track->sender->designation->title }})</span>
                                    @endif
                                </span>
                                <span class="timeline-date">{{ $track->created_at->format('M d, h:i A') }}</span>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;">
                                Routed to: <strong>{{ $track->receiver->name }}</strong>
                                ({{ $track->receiver->designation ? $track->receiver->designation->title : ($track->receiver->department ? $track->receiver->department->code : 'Admin') }})
                            </div>
                            <div>
                                <span class="badge badge-status-{{ $track->status_at_transfer }}" style="font-size: 0.68rem; padding: 2px 6px;">
                                    {{ ucfirst($track->status_at_transfer) }}
                                </span>
                            </div>
                            @if($track->remarks)
                                <div class="timeline-remarks">
                                    {{ $track->remarks }}
                                </div>
                            @endif

                            @if($track->attachment_path)
                                @php 
                                    $tExt = strtolower(pathinfo($track->attachment_path, PATHINFO_EXTENSION)); 
                                    $tUrl = route('attachments.download', ['type' => 'track', 'id' => $track->id]) . '?inline=true';
                                @endphp
                                <div style="margin-top: 8px; padding: 6px 10px; background-color: rgba(37,111,136,0.02); border: 1px dashed var(--border-color); border-radius: 4px; display: inline-flex; align-items: center; gap: 8px;">
                                    @if(in_array($tExt, ['jpg', 'jpeg', 'png', 'gif']))
                                        <a href="{{ $tUrl }}" target="_blank" style="display: flex; align-items: center; gap: 6px; text-decoration: none; color: var(--primary-color); font-weight: 500; font-size: 0.75rem;">
                                            <img src="{{ $tUrl }}" style="max-height: 30px; border-radius: 3px; border: 1px solid var(--border-color);" alt="Track image">
                                            <span>View Attached Image</span>
                                        </a>
                                    @elseif($tExt === 'pdf')
                                        <a href="{{ $tUrl }}" target="_blank" style="display: flex; align-items: center; gap: 6px; text-decoration: none; color: var(--primary-color); font-weight: 500; font-size: 0.75rem;">
                                            <i class="bi bi-file-earmark-pdf-fill" style="font-size: 1rem; color: #dc3545;"></i>
                                            <span>View Attached PDF</span>
                                        </a>
                                    @else
                                        <a href="{{ route('attachments.download', ['type' => 'track', 'id' => $track->id]) }}" style="display: flex; align-items: center; gap: 6px; text-decoration: none; color: var(--primary-color); font-weight: 500; font-size: 0.75rem;">
                                            <i class="bi bi-file-earmark-arrow-down-fill" style="font-size: 1rem;"></i>
                                            <span>Download Attachment ({{ strtoupper($tExt) }})</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Comments & Actions Section --}}
        <div class="dashboard-card" style="margin-bottom: 0;">
            <div class="card-title">
                <span>File Remarks & Discussion ({{ $unifiedComments->count() }})</span>
            </div>

            <!-- Full Width Comments Thread with Pagination -->
            <div class="comments-list" id="comments-container" style="width: 100%;">
                @if($unifiedComments->count() > 0)
                    @foreach($unifiedComments as $index => $comment)
                        @php
                            $isVc = false;
                            if ($comment->user) {
                                $uName = strtolower($comment->user->name ?? '');
                                $dCode = strtolower($comment->user->department->code ?? '');
                                $dName = strtolower($comment->user->department->name ?? '');
                                $desig = strtolower($comment->user->designation->title ?? '');

                                if ($dCode === 'vco' || str_contains($dName, 'vice chancellor') || str_contains($uName, 'vice chancellor') || str_contains($uName, 'vc ') || $uName === 'vc' || str_contains($desig, 'vice chancellor') || str_contains($desig, 'vc')) {
                                    $isVc = true;
                                }
                            }
                        @endphp

                        <div class="comment-card comment-item" data-comment-index="{{ $index }}" style="border-left: 5px solid {{ $isVc ? '#27ae60' : ($comment->type === 'track_remarks' ? 'var(--primary-color)' : '#95a5a6') }}; {{ $isVc ? 'background-color: rgba(39, 174, 96, 0.05);' : '' }}{{ $index >= 10 ? ' display: none;' : '' }}">
                            <div class="comment-meta">
                                <span class="comment-user" style="{{ $isVc ? 'color: #27ae60; font-weight: 700;' : '' }}">
                                    {{ $comment->user->name }}
                                    <span style="font-weight: 400; font-size: 0.75rem; color: var(--text-muted);">
                                        ({{ $comment->user->designation ? $comment->user->designation->title : ($comment->user->department ? $comment->user->department->code : 'Admin') }})
                                    </span>
                                    @if($isVc)
                                        <span class="badge" style="background-color: #27ae60; color: #ffffff; font-size: 0.68rem; padding: 2px 8px; margin-left: 6px; font-weight: 700; border-radius: 4px;">
                                            👑 VC Remarks
                                        </span>
                                    @endif
                                    @if($comment->type === 'track_remarks')
                                        <span class="badge badge-status-{{ $comment->status }}" style="font-size: 0.65rem; padding: 2px 6px; margin-left: 6px; font-weight: 700;">
                                            {{ ucfirst($comment->status) }}
                                        </span>
                                    @endif
                                </span>
                                <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="comment-text" style="{{ $isVc ? 'color: #1e8449; font-weight: 600;' : '' }}">{{ $comment->content }}</p>
                            
                            @if($comment->attachment_path)
                                @php 
                                    $cExt = strtolower(pathinfo($comment->attachment_path, PATHINFO_EXTENSION)); 
                                    $downloadUrl = route('attachments.download', ['type' => $comment->type === 'track_remarks' ? 'track' : 'comment', 'id' => $comment->id]);
                                    $cUrl = $downloadUrl . '?inline=true';
                                @endphp
                                <div style="margin-top: 10px;">
                                    @if(in_array($cExt, ['jpg', 'jpeg', 'png', 'gif']))
                                        <a href="{{ $cUrl }}" target="_blank" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 0.78rem; padding: 5px 12px; text-decoration: none;">
                                            <i class="bi bi-image" style="color: var(--primary-color);"></i>
                                            <span>View Attached Image</span>
                                        </a>
                                    @elseif($cExt === 'pdf')
                                        <a href="{{ $cUrl }}" target="_blank" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 0.78rem; padding: 5px 12px; text-decoration: none;">
                                            <i class="bi bi-file-earmark-pdf-fill" style="color: #dc3545;"></i>
                                            <span>View Attached PDF</span>
                                        </a>
                                    @else
                                        <a href="{{ $downloadUrl }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 0.78rem; padding: 5px 12px; text-decoration: none;">
                                            <i class="bi bi-file-earmark-arrow-down-fill" style="color: #7f8c8d;"></i>
                                            <span>Download Attachment ({{ strtoupper($cExt) }})</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if($unifiedComments->count() > 10)
                        @php
                            $totalPages = ceil($unifiedComments->count() / 10);
                        @endphp
                        <div id="comments-pagination-container" style="display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 20px; flex-wrap: wrap;">
                            <button type="button" onclick="changeCommentPage(currentCommentPage - 1)" id="btn-comment-prev" class="btn btn-secondary btn-sm" style="padding: 5px 12px; font-size: 0.8rem; font-weight: 600;" disabled>&laquo; Prev</button>
                            
                            @for($p = 1; $p <= $totalPages; $p++)
                                <button type="button" onclick="changeCommentPage({{ $p }})" class="btn btn-sm btn-comment-page-num" id="btn-comment-page-{{ $p }}" style="padding: 5px 12px; font-size: 0.8rem; font-weight: 600; {{ $p === 1 ? 'background-color: var(--primary-color); color: #fff; border-color: var(--primary-color);' : 'background-color: var(--white); color: var(--primary-color); border: 1px solid var(--border-color);' }}">
                                    {{ $p }}
                                </button>
                            @endfor

                            <button type="button" onclick="changeCommentPage(currentCommentPage + 1)" id="btn-comment-next" class="btn btn-secondary btn-sm" style="padding: 5px 12px; font-size: 0.8rem; font-weight: 600;">Next &raquo;</button>
                        </div>
                    @endif
                @else
                    <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.85rem;">
                        <i class="bi bi-chat-square-dots" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
                        <p>No remarks left on this document yet.</p>
                    </div>
                @endif
            </div>

            <!-- Bottom Row: Add Comment Form (Left) & Forward/Resolve Action Card (Right) -->
            <div style="display: grid; grid-template-columns: {{ ($document->current_holder_id === Auth::id() && $document->status !== 'completed') ? '1fr 360px' : '1fr' }}; gap: 25px; margin-top: 25px; padding-top: 25px; border-top: 1px solid var(--border-color);">
                
                <!-- Left: Write Comment Form -->
                <div>
                    <strong style="color: var(--primary-dark); display: block; margin-bottom: 12px; font-size: 0.9rem;">
                        <i class="bi bi-pencil-square"></i> Add Minute Remarks / Comment
                    </strong>
                    <form action="{{ route('documents.comment', $document->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <textarea name="content" class="form-control" placeholder="Add your official comment or minute remarks here..." style="min-height: 110px;" required></textarea>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-paperclip" style="color: var(--text-muted);"></i>
                                <input type="file" name="attachment" class="form-control" style="font-size: 0.8rem; padding: 4px 8px; border: 1px dashed var(--border-color); width: 230px;" accept="image/*,application/pdf">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm" style="padding: 7px 18px; font-weight: 600;">
                                <i class="bi bi-chat-fill"></i>
                                <span>Add Comment</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Forward / Resolve Actions Box -->
                @if($document->current_holder_id === Auth::id() && $document->status !== 'completed')
                    <div style="border: 2px solid var(--accent-color); border-radius: var(--border-radius); padding: 15px; background-color: #ffffff;">
                        <strong style="color: var(--primary-dark); display: block; margin-bottom: 12px; font-size: 0.9rem;">
                            <i class="bi bi-send-fill" style="color: var(--primary-color);"></i> File Routing Action
                        </strong>

                        <!-- Actions Header Tab Bar -->
                        <div style="display: flex; border-bottom: 1px solid var(--border-color); margin-bottom: 15px; margin-left: -15px; margin-right: -15px; margin-top: -15px; background-color: #fafafa;">
                            <button type="button" onclick="switchActionTab('forward')" id="tab-btn-forward" style="flex: 1; padding: 10px; background: none; border: none; font-weight: 700; font-size: 0.75rem; cursor: pointer; text-align: center; border-bottom: 3px solid var(--primary-color); color: var(--primary-color); display: flex; align-items: center; justify-content: center; gap: 5px; font-family: inherit;">
                                <i class="bi bi-send-fill"></i>
                                <span>Forward</span>
                            </button>
                            <button type="button" onclick="switchActionTab('resolve')" id="tab-btn-resolve" style="flex: 1; padding: 10px; background: none; border: none; font-weight: 700; font-size: 0.75rem; cursor: pointer; text-align: center; border-bottom: 3px solid transparent; color: #777; display: flex; align-items: center; justify-content: center; gap: 5px; font-family: inherit;">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Resolve</span>
                            </button>
                        </div>

                        <!-- Forward File Form -->
                        <div id="form-action-forward">
                            <form action="{{ route('documents.forward', $document->id) }}" method="POST">
                                @csrf
                                <div class="form-group" style="margin-bottom: 12px;">
                                    <label for="dept_select_action" class="form-label" style="font-size: 0.75rem;">Department</label>
                                    <select id="dept_select_action" class="form-control" style="padding: 6px 10px; font-size: 0.82rem;" onchange="filterUsersForAction(this.value)" required>
                                        <option value="">-- Choose Department --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group" style="margin-bottom: 12px;">
                                    <label for="receiver_id" class="form-label" style="font-size: 0.75rem;">Recipient</label>
                                    <select name="receiver_id" id="receiver_id" class="form-control" style="padding: 6px 10px; font-size: 0.82rem;" required disabled>
                                        <option value="">-- Choose Staff --</option>
                                    </select>
                                </div>

                                <div class="form-group" style="margin-bottom: 12px;">
                                    <label for="status" class="form-label" style="font-size: 0.75rem;">Action</label>
                                    <select name="status" id="status" class="form-control" style="padding: 6px 10px; font-size: 0.82rem;" required>
                                        <option value="forwarded" selected>Forwarded</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-accent" style="width: 100%; justify-content: center; font-weight: 700; font-size: 0.82rem; padding: 8px;">
                                    <i class="bi bi-send-fill"></i>
                                    <span>Forward Document</span>
                                </button>
                            </form>
                        </div>

                        <!-- Complete/Resolve File Form -->
                        <div id="form-action-resolve" style="display: none;">
                            <form action="{{ route('documents.status', $document->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="completed">
                                
                                <div class="form-group" style="margin-bottom: 12px;">
                                    <label for="complete_remarks" class="form-label" style="font-size: 0.75rem;">Final Resolution Note</label>
                                    <textarea name="remarks" id="complete_remarks" class="form-control" style="min-height: 60px; padding: 6px 10px; font-size: 0.82rem;" placeholder="Final actions taken..." required></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; background-color: var(--priority-normal); font-weight: 700; font-size: 0.82rem; padding: 8px;">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Resolve & Close File</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleTrackingTrail() {
            const section = document.getElementById('tracking-trail-section');
            const btn = document.getElementById('btn-tracking-toggle');
            if (section.style.display === 'none') {
                section.style.display = 'block';
                btn.innerHTML = '<i class="bi bi-signpost-2-fill"></i> <span>Hide Tracking Trail</span>';
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                section.style.display = 'none';
                btn.innerHTML = '<i class="bi bi-signpost-2-fill"></i> <span>Show Tracking Trail</span>';
            }
        }

        let currentCommentPage = 1;
        const totalCommentPages = {{ ceil($unifiedComments->count() / 10) }};

        function changeCommentPage(page) {
            if (page < 1 || page > totalCommentPages) return;
            currentCommentPage = page;

            const startIndex = (page - 1) * 10;
            const endIndex = startIndex + 10;

            const items = document.querySelectorAll('.comment-item');
            items.forEach(function(item) {
                const idx = parseInt(item.getAttribute('data-comment-index'));
                if (idx >= startIndex && idx < endIndex) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });

            // Update page number button active styles
            for (let p = 1; p <= totalCommentPages; p++) {
                const btn = document.getElementById('btn-comment-page-' + p);
                if (btn) {
                    if (p === page) {
                        btn.style.backgroundColor = 'var(--primary-color)';
                        btn.style.color = '#ffffff';
                        btn.style.borderColor = 'var(--primary-color)';
                    } else {
                        btn.style.backgroundColor = 'var(--white)';
                        btn.style.color = 'var(--primary-color)';
                        btn.style.borderColor = 'var(--border-color)';
                    }
                }
            }

            // Update Prev / Next buttons disabled states
            const prevBtn = document.getElementById('btn-comment-prev');
            const nextBtn = document.getElementById('btn-comment-next');
            if (prevBtn) prevBtn.disabled = (page === 1);
            if (nextBtn) nextBtn.disabled = (page === totalCommentPages);

            // Scroll smoothly to top of comments thread
            const container = document.getElementById('comments-container');
            if (container) {
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    </script>

    @if($document->current_holder_id === Auth::id() && $document->status !== 'completed')
        <script>
            const actionUsers = @json($users);

            function filterUsersForAction(deptId) {
                const userSelect = document.getElementById('receiver_id');
                
                // Clear existing
                userSelect.innerHTML = '<option value="">-- Choose Staff --</option>';
                
                if (!deptId) {
                    userSelect.disabled = true;
                    return;
                }

                // Filter users
                const filteredUsers = actionUsers.filter(user => user.department_id == deptId);

                if (filteredUsers.length === 0) {
                    userSelect.innerHTML = '<option value="">No staff found</option>';
                    userSelect.disabled = true;
                    return;
                }

                filteredUsers.forEach(user => {
                    const opt = document.createElement('option');
                    opt.value = user.id;
                    const desig = user.designation ? user.designation.title : null;
                    opt.textContent = desig ? `${desig} (${user.name})` : user.name;
                    userSelect.appendChild(opt);
                });

                userSelect.disabled = false;
            }

            function switchActionTab(tabId) {
                const btnForward = document.getElementById('tab-btn-forward');
                const btnResolve = document.getElementById('tab-btn-resolve');
                const formForward = document.getElementById('form-action-forward');
                const formResolve = document.getElementById('form-action-resolve');
                
                if (tabId === 'forward') {
                    formForward.style.display = 'block';
                    formResolve.style.display = 'none';
                    btnForward.style.borderBottomColor = 'var(--primary-color)';
                    btnForward.style.color = 'var(--primary-color)';
                    btnResolve.style.borderBottomColor = 'transparent';
                    btnResolve.style.color = '#777';
                } else {
                    formForward.style.display = 'none';
                    formResolve.style.display = 'block';
                    btnResolve.style.borderBottomColor = 'var(--primary-color)';
                    btnResolve.style.color = 'var(--primary-color)';
                    btnForward.style.borderBottomColor = 'transparent';
                    btnForward.style.color = '#777';
                }
            }
        </script>
    @endif
@endsection
