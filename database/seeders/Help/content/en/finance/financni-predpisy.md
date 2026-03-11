# Payment Charges and Tariffs

Charges in the KS system determine how much each member must pay for a given period or event. They are the counterpart to payments, and their correct configuration is essential for monitoring a member's balance (debt or overpayment).

### Tariffs (Price Lists)
Tariffs are templates for charges. Instead of manually entering an amount for each player, you create a tariff (e.g., "U11 Membership Fee - Fall").
- **Name:** A clear name (also visible to the member).
- **Amount:** The amount to be paid.
- **Season:** The season the tariff belongs to.
- **Categories:** The types of members the tariff is intended for.

### Bulk Generation of Charges
In the **Charges** section, you can use the **Generate Charges** action.
- Select the tariff and the season.
- The system scans all active members matching the tariff and automatically issues a charge to each.
- Each charge receives a unique **Variable Symbol (VS)** linked to the member's profile.

### Manual Adjustments and Discounts
Sometimes a charge needs to be adjusted individually:
- **Discounts:** You can modify the amount on a specific charge (e.g., sibling discount, financial aid).
- **Cancellation (Storno):** If a charge was issued incorrectly, it can be cancelled. The system will then no longer demand payment for that charge.

### Charge Statuses
- **Unpaid:** The member has not made any payment towards the charge yet.
- **Partially Paid:** A payment allocation has been made, but it did not cover the full amount.
- **Paid:** The charge is fully covered by one or more payments.
- **Overpaid:** More money has been allocated to the charge than the original amount (the system tracks this).

### Recommended Workflow
1. First, create **Tariffs** for the entire season.
2. At the beginning of each half-year, run **Bulk Generation**.
3. Continuously monitor the **Balance** of members, which the system calculates as `Total Payments - Total Charges`.
