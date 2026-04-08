USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_StaffDetailsByscheduledPersonID]    Script Date: 20/05/2022 13:47:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE  [dbo].[usp_get_StaffDetailsByscheduledPersonID]
	-- Add the parameters for the stored procedure here navi
	@scheduledPersonID INT = 0 
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
    SET NOCOUNT ON;
    SELECT CASE WHEN (sp.DisplayName IS NULL) THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '')
                THEN (sd.Forename + ' ' + sd.Surname) ELSE (sd.PreferredForename + ' ' + sd.Surname)    END
        ELSE sp.DisplayName END AS userDisplayName,sd.NetLogin
     from StaffDetails sd (nolock) 
		left join ScheduledPeople sp (nolock) on sp.StaffDetailsID = sd.StaffID
		where sp.ScheduledPersonID = @scheduledPersonID 
END

