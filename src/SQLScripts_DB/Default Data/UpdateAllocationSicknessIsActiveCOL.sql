update AllocationSickness  set IsActive = 1 

update  als
set als.IsActive= 0
From AllocationSickness als
JOIN Allocations a on als.AllocationID =a.ID
where a.DutyName Like '%Restore%' and a.MarkedSickness =1 

update Allocations  set MarkedSickness = 0 where DutyName Like '%Restore%' and MarkedSickness =1 