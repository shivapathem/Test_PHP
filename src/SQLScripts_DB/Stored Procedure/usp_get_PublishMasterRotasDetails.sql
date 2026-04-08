USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_PublishMasterRotasDetails]    Script Date: 30/03/2026 14:23:46 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_get_PublishMasterRotasDetails]
	-- Add the parameters for the stored procedure here
	@teamid int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- select statements for procedure here
select  rd.RotaWeek, rp.StartWeek, mr.WeeksInRota, (rp.StartWeek + mr.WeeksInRota) as EndWeek,
        md.MasterDutyID, md.DutyName,stl.ScheduledPersonID as SchedulingPersonID, rd.RotaWeek, rd.DOTW,
        md.BackColour, md.ForeColour, md.StartTime, md.EndTime, stl.TeamID as SchedulingTeamId,
        stl.IsHomeTeam,CASE WHEN sp.StaffDetailsID IS NULL OR sp.StaffDetailsID = 0 THEN sp.DisplayName ELSE sd.Forename+' '+ sd.Surname END  DisplayName,
        sd.StaffID,sd.StaffNumber,sd.NetLogin, ISNULL(stl.fontcolour,'#000000') AS StaffTextColour,stl.SortCode
from RotaPeople as rp
         inner join ScheduledPeople sp on sp.ScheduledPersonID = rp.ScheduledPersonID
         inner join ScheduledPersonTeam_LINK as stl on sp.ScheduledPersonID = stl.ScheduledPersonID
    and (stl.IsHomeTeam = 1 or (stl.IsHomeTeam IN (0,2) and stl.IsActive = 1) )
         inner join MasterRotas as mr on mr.RotaID = rp.RotaID
         inner join RotaDuties as rd (NOLOCK) ON rd.RotaID = mr.RotaID
         inner join MasterDuties as md on md.MasterDutyID = rd.MasterDutyID
         left join StaffDetails sd on sd.StaffID = sp.StaffDetailsID
where stl.TeamID = @teamid  and mr.IsExported = 1


END