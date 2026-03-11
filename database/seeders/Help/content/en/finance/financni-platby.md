# Evidence and Payment Import

The KS financial module tracks all club income. Proper payment evidence is crucial for monitoring member debts and generating data for accounting.

### Bank Imports (Critical)
The most efficient way to enter payments is by bulk importing a bank statement.
- **Action:** In the payment overview, use the **Import payments** button.
- **Format:** The system supports standard CSV/GPC bank statement formats (Fio, Partners, etc.).
- **Process:** The system will attempt to automatically pair the payment with a member based on the **Variable Symbol (VS)** or the account name in the remarks.
- **Important:** Always check "Unpaired payments" after an import. You must assign these manually.

### Payment Types
A payment can be entered into the system in several ways:
1. **Bank Transfer:** Automatically imported or entered manually with the date it hit the account.
2. **Cash:** Entered by the treasurer or coach when collecting money at training or an event.
3. **Overpayment / Internal:** Used for moving funds between seasons or correcting errors.

### Manual Payment Entry
When entering a payment manually, pay attention to the following fields:
- **Member (User):** Who the payment belongs to.
- **Amount:** The actual amount received.
- **Date:** The day the money was received.
- **Variable Symbol:** If provided, it will simplify future pairing.

### Payment Statuses
- **Unconfirmed:** The payment is pending approval by an administrator (e.g., for manual entries by coaches).
- **Confirmed:** The payment is valid and counts towards the member's balance.
- **Cancelled:** A payment that was entered incorrectly or was returned as failed.

### Common Mistakes
- **Incorrect Variable Symbol:** If a player uses the wrong VS, the system will not pair the payment. You must find it manually in the unpaired payments list and assign it to the correct user.
- **Duplicate Import:** The system tries to prevent duplicates by checking transaction IDs, but be careful when entering manually.
