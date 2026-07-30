@php
    $u = Auth::user();
    
    // 1. Files Created: Open/active files created by logged in user (excluding completed)
    $statMyFiles = \App\Models\Document::where('creator_id', $u->id)
        ->whereNotIn('status', ['draft', 'completed'])
        ->count();

    // 2. Marked to Me: Active files assigned to logged in user
    $statMarkedToMe = \App\Models\Document::where('current_holder_id', $u->id)
        ->whereNotIn('status', ['draft', 'completed'])
        ->count();

    // 3. Closed Files: Completed files (matching index tab=completed logic)
    $closedQuery = \App\Models\Document::where('status', 'completed');
    if (!$u->canViewAllFiles()) {
        $closedQuery->where(function($q) use ($u) {
            $q->where('creator_id', $u->id)
              ->orWhere('current_holder_id', $u->id)
              ->orWhereHas('tracks', function($t) use ($u) {
                  $t->where('sender_id', $u->id)->orWhere('receiver_id', $u->id);
              });
        });
    }
    $statClosedFiles = $closedQuery->count();

    // 4. Open Files: Active open files where user is directly involved
    $statOpenFiles = \App\Models\Document::whereNotIn('status', ['draft', 'completed'])
        ->where(function($q) use ($u) {
            $q->where('creator_id', $u->id)
              ->orWhere('current_holder_id', $u->id)
              ->orWhereHas('tracks', function($t) use ($u) {
                  $t->where('sender_id', $u->id)->orWhere('receiver_id', $u->id);
              });
        })
        ->count();

    // 5. Comments I Made
    $statComments = \App\Models\Comment::where('user_id', $u->id)->count();

    // Active Route & Tab Detection
    $isRouteMyFiles = request()->routeIs('documents.myFiles');
    $isRouteIndex   = request()->routeIs('documents.index');
    $currentTab     = request()->query('tab', 'inbox');
    $isRouteRemarks = request()->routeIs('documents.myRemarks');

    $activeCard1 = $isRouteMyFiles;
    $activeCard2 = $isRouteIndex && ($currentTab === 'inbox');
    $activeCard3 = $isRouteIndex && ($currentTab === 'completed');
    $activeCard4 = $isRouteIndex && ($currentTab === 'open');
    $activeCard5 = $isRouteRemarks;
@endphp

@php
    $u = Auth::user();
    
    // 1. Files Created: Open/active files created by logged in user (excluding completed)
    $statMyFiles = \App\Models\Document::where('creator_id', $u->id)
        ->whereNotIn('status', ['draft', 'completed'])
        ->count();

    // 2. Marked to Me: Active files assigned to logged in user
    $statMarkedToMe = \App\Models\Document::where('current_holder_id', $u->id)
        ->whereNotIn('status', ['draft', 'completed'])
        ->count();

    // 3. Closed Files: Completed files (matching index tab=completed logic)
    $closedQuery = \App\Models\Document::where('status', 'completed');
    if (!$u->canViewAllFiles()) {
        $closedQuery->where(function($q) use ($u) {
            $q->where('creator_id', $u->id)
              ->orWhere('current_holder_id', $u->id)
              ->orWhereHas('tracks', function($t) use ($u) {
                  $t->where('sender_id', $u->id)->orWhere('receiver_id', $u->id);
              });
        });
    }
    $statClosedFiles = $closedQuery->count();

    // 4. Open Files: Active open files where user is directly involved
    $statOpenFiles = \App\Models\Document::whereNotIn('status', ['draft', 'completed'])
        ->where(function($q) use ($u) {
            $q->where('creator_id', $u->id)
              ->orWhere('current_holder_id', $u->id)
              ->orWhereHas('tracks', function($t) use ($u) {
                  $t->where('sender_id', $u->id)->orWhere('receiver_id', $u->id);
              });
        })
        ->count();

    // 5. Comments I Made
    $statComments = \App\Models\Comment::where('user_id', $u->id)->count();

    // Active Route & Tab Detection
    $isRouteMyFiles = request()->routeIs('documents.myFiles');
    $isRouteIndex   = request()->routeIs('documents.index');
    $currentTab     = request()->query('tab', 'inbox');
    $isRouteRemarks = request()->routeIs('documents.myRemarks');

    $activeCard1 = $isRouteMyFiles;
    $activeCard2 = $isRouteIndex && ($currentTab === 'inbox');
    $activeCard3 = $isRouteIndex && ($currentTab === 'completed');
    $activeCard4 = $isRouteIndex && ($currentTab === 'open');
    $activeCard5 = $isRouteRemarks;
@endphp

<!-- 5 Stat Cards Bar (High Contrast Active Card Highlighting) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 16px; margin-bottom: 25px;">
    <!-- 1. Files Created (Blue) -->
    <a href="{{ route('documents.myFiles') }}" 
       title="Files initiated and created by your office"
       style="{{ $activeCard1 
           ? 'background: linear-gradient(135deg, #2980b9 0%, #1f618d 100%); border: 2px solid #1f618d; box-shadow: 0 10px 25px rgba(41, 128, 185, 0.38); transform: translateY(-4px);' 
           : 'background: #ffffff; border: 1px solid #e2e8f0; border-left: 5px solid #2980b9; box-shadow: 0 2px 8px rgba(0,0,0,0.03);' }} border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; text-decoration: none; transition: all 0.25s ease;" 
       onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 25px rgba(41, 128, 185, 0.35)';" 
       onmouseout="this.style.transform='{{ $activeCard1 ? 'translateY(-4px)' : 'translateY(0)' }}'; this.style.boxShadow='{{ $activeCard1 ? '0 10px 25px rgba(41, 128, 185, 0.38)' : '0 2px 8px rgba(0,0,0,0.03)' }}';">
        <div>
            <div style="font-size: 2.1rem; font-weight: 800; color: {{ $activeCard1 ? '#ffffff' : '#2980b9' }}; line-height: 1; letter-spacing: -0.5px;">{{ $statMyFiles }}</div>
            <div style="font-size: 0.88rem; font-weight: 700; color: {{ $activeCard1 ? '#ffffff' : '#34495e' }}; margin-top: 6px; letter-spacing: 0.2px;">Files Created</div>
            <div style="font-size: 0.68rem; color: {{ $activeCard1 ? 'rgba(255,255,255,0.85)' : '#7f8c8d' }}; margin-top: 3px; font-weight: 500;">Files created by you</div>
        </div>
        <div style="width: 50px; height: 50px; border-radius: 50%; background: {{ $activeCard1 ? 'rgba(255, 255, 255, 0.22)' : 'linear-gradient(135deg, rgba(52, 152, 219, 0.2) 0%, rgba(41, 128, 185, 0.1) 100%)' }}; display: flex; align-items: center; justify-content: center; color: {{ $activeCard1 ? '#ffffff' : '#2980b9' }}; font-size: 1.4rem;">
            <i class="bi bi-file-earmark-text-fill"></i>
        </div>
    </a>

    <!-- 2. Marked to Me (Orange) -->
    <a href="{{ route('documents.index', ['tab' => 'inbox']) }}" 
       title="Files currently assigned to you for review or action"
       style="{{ $activeCard2 
           ? 'background: linear-gradient(135deg, #d35400 0%, #a04000 100%); border: 2px solid #a04000; box-shadow: 0 10px 25px rgba(211, 84, 0, 0.38); transform: translateY(-4px);' 
           : 'background: #ffffff; border: 1px solid #e2e8f0; border-left: 5px solid #d35400; box-shadow: 0 2px 8px rgba(0,0,0,0.03);' }} border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; text-decoration: none; transition: all 0.25s ease;" 
       onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 25px rgba(211, 84, 0, 0.35)';" 
       onmouseout="this.style.transform='{{ $activeCard2 ? 'translateY(-4px)' : 'translateY(0)' }}'; this.style.boxShadow='{{ $activeCard2 ? '0 10px 25px rgba(211, 84, 0, 0.38)' : '0 2px 8px rgba(0,0,0,0.03)' }}';">
        <div>
            <div style="font-size: 2.1rem; font-weight: 800; color: {{ $activeCard2 ? '#ffffff' : '#d35400' }}; line-height: 1; letter-spacing: -0.5px;">{{ $statMarkedToMe }}</div>
            <div style="font-size: 0.88rem; font-weight: 700; color: {{ $activeCard2 ? '#ffffff' : '#34495e' }}; margin-top: 6px; letter-spacing: 0.2px;">Marked to Me</div>
            <div style="font-size: 0.68rem; color: {{ $activeCard2 ? 'rgba(255,255,255,0.85)' : '#7f8c8d' }}; margin-top: 3px; font-weight: 500;">Pending your action</div>
        </div>
        <div style="width: 50px; height: 50px; border-radius: 50%; background: {{ $activeCard2 ? 'rgba(255, 255, 255, 0.22)' : 'linear-gradient(135deg, rgba(243, 156, 18, 0.2) 0%, rgba(211, 84, 0, 0.1) 100%)' }}; display: flex; align-items: center; justify-content: center; color: {{ $activeCard2 ? '#ffffff' : '#d35400' }}; font-size: 1.4rem;">
            <i class="bi bi-person-badge-fill"></i>
        </div>
    </a>

    <!-- 3. Closed Files (Green) -->
    <a href="{{ route('documents.index', ['tab' => 'completed']) }}" 
       title="Files that have been completed and officially archived"
       style="{{ $activeCard3 
           ? 'background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%); border: 2px solid #1e8449; box-shadow: 0 10px 25px rgba(39, 174, 96, 0.38); transform: translateY(-4px);' 
           : 'background: #ffffff; border: 1px solid #e2e8f0; border-left: 5px solid #27ae60; box-shadow: 0 2px 8px rgba(0,0,0,0.03);' }} border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; text-decoration: none; transition: all 0.25s ease;" 
       onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 25px rgba(39, 174, 96, 0.35)';" 
       onmouseout="this.style.transform='{{ $activeCard3 ? 'translateY(-4px)' : 'translateY(0)' }}'; this.style.boxShadow='{{ $activeCard3 ? '0 10px 25px rgba(39, 174, 96, 0.38)' : '0 2px 8px rgba(0,0,0,0.03)' }}';">
        <div>
            <div style="font-size: 2.1rem; font-weight: 800; color: {{ $activeCard3 ? '#ffffff' : '#27ae60' }}; line-height: 1; letter-spacing: -0.5px;">{{ $statClosedFiles }}</div>
            <div style="font-size: 0.88rem; font-weight: 700; color: {{ $activeCard3 ? '#ffffff' : '#34495e' }}; margin-top: 6px; letter-spacing: 0.2px;">Closed Files</div>
            <div style="font-size: 0.68rem; color: {{ $activeCard3 ? 'rgba(255,255,255,0.85)' : '#7f8c8d' }}; margin-top: 3px; font-weight: 500;">Completed & archived</div>
        </div>
        <div style="width: 50px; height: 50px; border-radius: 50%; background: {{ $activeCard3 ? 'rgba(255, 255, 255, 0.22)' : 'linear-gradient(135deg, rgba(46, 204, 113, 0.2) 0%, rgba(39, 174, 96, 0.1) 100%)' }}; display: flex; align-items: center; justify-content: center; color: {{ $activeCard3 ? '#ffffff' : '#27ae60' }}; font-size: 1.4rem;">
            <i class="bi bi-check-circle-fill"></i>
        </div>
    </a>

    <!-- 4. Open Files (Red) -->
    <a href="{{ route('documents.index', ['tab' => 'open']) }}" 
       title="All active ongoing files currently processing across offices"
       style="{{ $activeCard4 
           ? 'background: linear-gradient(135deg, #c0392b 0%, #922b21 100%); border: 2px solid #922b21; box-shadow: 0 10px 25px rgba(192, 57, 43, 0.38); transform: translateY(-4px);' 
           : 'background: #ffffff; border: 1px solid #e2e8f0; border-left: 5px solid #c0392b; box-shadow: 0 2px 8px rgba(0,0,0,0.03);' }} border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; text-decoration: none; transition: all 0.25s ease;" 
       onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 25px rgba(192, 57, 43, 0.35)';" 
       onmouseout="this.style.transform='{{ $activeCard4 ? 'translateY(-4px)' : 'translateY(0)' }}'; this.style.boxShadow='{{ $activeCard4 ? '0 10px 25px rgba(192, 57, 43, 0.38)' : '0 2px 8px rgba(0,0,0,0.03)' }}';">
        <div>
            <div style="font-size: 2.1rem; font-weight: 800; color: {{ $activeCard4 ? '#ffffff' : '#c0392b' }}; line-height: 1; letter-spacing: -0.5px;">{{ $statOpenFiles }}</div>
            <div style="font-size: 0.88rem; font-weight: 700; color: {{ $activeCard4 ? '#ffffff' : '#34495e' }}; margin-top: 6px; letter-spacing: 0.2px;">Open Files</div>
            <div style="font-size: 0.68rem; color: {{ $activeCard4 ? 'rgba(255,255,255,0.85)' : '#7f8c8d' }}; margin-top: 3px; font-weight: 500;">Active in workflow</div>
        </div>
        <div style="width: 50px; height: 50px; border-radius: 50%; background: {{ $activeCard4 ? 'rgba(255, 255, 255, 0.22)' : 'linear-gradient(135deg, rgba(231, 76, 60, 0.2) 0%, rgba(192, 57, 43, 0.1) 100%)' }}; display: flex; align-items: center; justify-content: center; color: {{ $activeCard4 ? '#ffffff' : '#c0392b' }}; font-size: 1.4rem;">
            <i class="bi bi-folder2-open"></i>
        </div>
    </a>

    <!-- 5. Comments I Made (Teal) -->
    <a href="{{ route('documents.myRemarks') }}" 
       title="History of remarks and comments posted by you on files"
       style="{{ $activeCard5 
           ? 'background: linear-gradient(135deg, #16a085 0%, #117864 100%); border: 2px solid #117864; box-shadow: 0 10px 25px rgba(22, 160, 133, 0.38); transform: translateY(-4px);' 
           : 'background: #ffffff; border: 1px solid #e2e8f0; border-left: 5px solid #16a085; box-shadow: 0 2px 8px rgba(0,0,0,0.03);' }} border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; text-decoration: none; transition: all 0.25s ease;" 
       onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 25px rgba(22, 160, 133, 0.35)';" 
       onmouseout="this.style.transform='{{ $activeCard5 ? 'translateY(-4px)' : 'translateY(0)' }}'; this.style.boxShadow='{{ $activeCard5 ? '0 10px 25px rgba(22, 160, 133, 0.38)' : '0 2px 8px rgba(0,0,0,0.03)' }}';">
        <div>
            <div style="font-size: 2.1rem; font-weight: 800; color: {{ $activeCard5 ? '#ffffff' : '#16a085' }}; line-height: 1; letter-spacing: -0.5px;">{{ $statComments }}</div>
            <div style="font-size: 0.88rem; font-weight: 700; color: {{ $activeCard5 ? '#ffffff' : '#34495e' }}; margin-top: 6px; letter-spacing: 0.2px;">Comments I Made</div>
            <div style="font-size: 0.68rem; color: {{ $activeCard5 ? 'rgba(255,255,255,0.85)' : '#7f8c8d' }}; margin-top: 3px; font-weight: 500;">Your remarks log</div>
        </div>
        <div style="width: 50px; height: 50px; border-radius: 50%; background: {{ $activeCard5 ? 'rgba(255, 255, 255, 0.22)' : 'linear-gradient(135deg, rgba(26, 188, 156, 0.2) 0%, rgba(22, 160, 133, 0.1) 100%)' }}; display: flex; align-items: center; justify-content: center; color: {{ $activeCard5 ? '#ffffff' : '#16a085' }}; font-size: 1.4rem;">
            <i class="bi bi-chat-left-text-fill"></i>
        </div>
    </a>
</div>
