@if($transfer->transfer_type === 'transfer_out')
    <span class="badge bg-danger">Transfer Out</span>
@elseif($transfer->transfer_type === 'transfer_in')
    <span class="badge bg-success">Transfer In</span>
@elseif($transfer->transfer_type === 're_admission')
    <span class="badge bg-warning text-dark">Re-admission</span>
@else
    <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $transfer->transfer_type)) }}</span>
@endif