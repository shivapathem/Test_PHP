update LeaveAllocation set  IsCarryOver = 0

update LeaveAllocation set  IsCarryOver = 1 where Comments like '%carry%over%'
