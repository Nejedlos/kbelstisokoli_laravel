# Allocation and Payment Pairing

Allocation is the process where the system "assigns" money received from a **Payment** to a specific **Charge**. Only after a successful allocation does the charge status change to "Paid," and the member's debt is cleared.

### Automatic Pairing
The system saves time by automatically pairing most payments during the import process:
1. **By Variable Symbol (VS):** If the VS on the payment exactly matches the VS on the charge, the system creates the allocation immediately.
2. **By Member:** If a member sends money without a VS but has only one unpaid charge in the given season, the system may suggest the payment for allocation.

### Manual Allocation (Technical Procedure)
If a payment was not paired automatically (e.g., missing VS), you must assign it manually:
1. Go to the **Payments** section and find the specific payment.
2. In the payment details (or through the action menu in the table), locate the **Allocations** section.
3. Click on **Create Allocation**.
4. Select the charge that is to be covered by this payment.
5. Enter the allocation amount (a payment can cover multiple charges, such as for siblings).

### Handling Amounts
- **Partial Allocation:** If a member sends only part of the money, allocate that portion. The charge will remain in the "Partially Paid" state.
- **Overpayment:** If a member sends more than required, the system will allocate the full amount of the charge. The remainder of the payment will remain "unallocated" and can be kept as a credit for a future charge or refunded.

### Deleting an Allocation
If you assigned a payment to the wrong member or the wrong charge, you can delete the allocation:
- **Consequence:** Deleting an allocation makes the charge "Unpaid" again and return the payment to an "Unallocated" state. The money is not deleted, just freed up for a different assignment.

### Why is it Important?
Without an allocation, the system doesn't know that a specific charge is paid, even if you already see the money in the bank statement. The member section will then continue to report a debt to the player.
