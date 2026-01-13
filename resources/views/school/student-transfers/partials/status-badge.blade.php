@if($transfer->status === 'pending')
    <span class="badge bg-warning text-dark">Pending</span>
@elseif($transfer->status === 'approved')
    <span class="badge bg-info">Approved</span>
@elseif($transfer->status === 'completed')
    <span class="badge bg-success">Completed</span>
@elseif($transfer->status === 'cancelled')
    <span class="badge bg-danger">Cancelled</span>
@else
    <span class="badge bg-secondary">{{ ucfirst($transfer->status) }}</span>
@endif