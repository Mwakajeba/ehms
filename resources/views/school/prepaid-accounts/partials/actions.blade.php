<div class="btn-group" role="group">
    <a href="{{ route('school.prepaid-accounts.show', $account->hashid) }}" class="btn btn-sm btn-info" title="View Details">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('school.prepaid-accounts.edit', $account->hashid) }}" class="btn btn-sm btn-warning" title="Edit Account">
        <i class="bx bx-edit"></i>
    </a>
    <button type="button" class="btn btn-sm btn-primary" title="Add Credit" onclick="showAddCreditModal('{{ $account->hashid }}', '{{ addslashes($account->student->first_name . ' ' . $account->student->last_name) }}')">
        <i class="bx bx-plus"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger" title="Delete Account" onclick="deleteAccount('{{ $account->hashid }}', '{{ addslashes($account->student->first_name . ' ' . $account->student->last_name) }}')">
        <i class="bx bx-trash"></i>
    </button>
</div>

