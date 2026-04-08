USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ChargeDutyWeeklyAllocation]    Script Date: 05/02/2024 06:35:21 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_get_ChargeDutyWeeklyAllocation]
	-- Add the parameters for the stored procedure here
	@ScheduledPersonID int,
	@MasterDutyId int,
	@AllocationID int,
	@StartDate varchar(50),
	@UserId int,
	@teamId int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
    -- Insert statements for procedure here
	select 
		cdm.ChargingId,
		cdm.EstabCodeId, ec.EstablishCode,
		cdm.ActivityCodeId, ac.ActivityCodeName,
		cdm.ChargeCodeId, cwc.ChargeWbsCodeName,
		cdm.Quantity, cdm.UnitPrice, (cdm.UnitPrice * cdm.Quantity) TotalPrice,
		case when cdm.IsActual = 0 then 'Provisional'
		when cdm.IsActual = 2 then 'Hold'
			Else 'Actual' end as Status,
		case when cdm.IsSentToFinance = 0 then 'No'
			Else 'Yes' end as 'SentToFinance',
		 cdm.Comments, cdm.Contact, cdm.Telephone, cdm.IsActual,
		 cdm.IsSentToFinance,cwc.CodeType,
		 case when isnull(pcd.UserId,'') = '' then 0
		 else 1 end as MaintainCharging,
		 @UserId as UserId,
		 cdm.ChargingId as MaintainChargingId,
		 cdm.MasterDutyId,
		 cdm.AllocationId
	from ChargingDutyMapping_Link cdm
		inner join EstablishCode ec on ec.EstablishCodeId = cdm.EstabCodeId
		inner join ActivityCode ac on ac.ActivityCodeId = cdm.ActivityCodeId --and ac.IsActive = 1
		inner join ChargeWbsCode cwc on cwc.ChargeWbsCodeId = cdm.ChargeCodeId and cwc.IsActive = 1
		left join PrefilledChargeDetails_Link pcd on pcd.UserId = @UserId and pcd.ChargingId = cdm.ChargingId and pcd.SchedulingTeamId = @teamId
	where (cdm.PersonId = @ScheduledPersonID
		and convert(datetime,cdm.ChargingDutyDate,103) = convert(datetime,@StartDate,103)
		and AllocationID = @AllocationID)
	union
	select 
		cdm.ChargingId,
		cdm.EstabCodeId, ec.EstablishCode,
		cdm.ActivityCodeId, ac.ActivityCodeName,
		cdm.ChargeCodeId, cwc.ChargeWbsCodeName,
		cdm.Quantity, cdm.UnitPrice, (cdm.UnitPrice * cdm.Quantity) TotalPrice,
		case when cdm.IsActual = 0 then 'Provisional'
			when cdm.IsActual = 2 then 'Hold'
			Else 'Actual' end as Status,
		case when cdm.IsSentToFinance = 0 then 'No'
			Else 'Yes' end as 'SentToFinance',
		 cdm.Comments, cdm.Contact, cdm.Telephone, cdm.IsActual,
		 cdm.IsSentToFinance,cwc.CodeType,
		 case when isnull(pcd.UserId,'') = '' then 0
		 else 1 end as MaintainCharging,
		 isnull(pcd.UserId,0) as UserId,
		 isnull(pcd.ChargingId,0) as MaintainChargingId,
		 cdm.MasterDutyId,
		 cdm.AllocationId
	from ChargingDutyMapping_Link cdm
		inner join EstablishCode ec on ec.EstablishCodeId = cdm.EstabCodeId
		inner join ActivityCode ac on ac.ActivityCodeId = cdm.ActivityCodeId 
		inner join ChargeWbsCode cwc on cwc.ChargeWbsCodeId = cdm.ChargeCodeId and cwc.IsActive = 1
		inner join PrefilledChargeDetails_Link pcd on pcd.UserId = @UserId and pcd.ChargingId = cdm.ChargingId and pcd.SchedulingTeamId = @teamId
	where pcd.ChargingId not in (select ChargingId from ChargingDutyMapping_Link where (cdm.PersonId = @ScheduledPersonID
		and convert(datetime,cdm.ChargingDutyDate,103) = convert(datetime,@StartDate,103)
		and AllocationID = @AllocationID))
	
END
