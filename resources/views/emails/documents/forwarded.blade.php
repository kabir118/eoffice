<x-mail::message>
# New File / Task Assigned

Dear User,

A new administrative file/task has been assigned to your desk in the UST Bannu e-Office portal.

### 📋 Assignment Details:
- **Assigned by:** {{ $senderName }}
- **File Subject:** {{ $document->title }}
- **Assigned Date:** {{ $document->updated_at ? $document->updated_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A') }}
- **File Priority:** {{ ucfirst(str_replace('_', ' ', $document->priority)) }}
- **Tracking ID:** {{ $document->file_number }}

@if(!empty($remarks) && !in_array($remarks, ['File forwarded.', 'File initiated and forwarded.']))
### 💬 Minute Remarks / Notes:
> {{ $remarks }}
@endif

Please click the button below to view the file and take necessary action.

<x-mail::button :url="route('documents.show', $document->id)">
Open File in e-Office
</x-mail::button>

Regards,  
**UST Bannu e-Office Portal**
</x-mail::message>
