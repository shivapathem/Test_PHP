USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_GetStaffLeaveGroupDetail]    Script Date: 31/10/2022 11:43:37 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER       PROCEDURE [dbo].[usp_get_GetStaffLeaveGroupDetail]

@levegroupid  INT,
@NetLogin varchar(50)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	SELECT  swcl.EFT, swcl.EFT1, swcl.EFTSummer, swcl.EFTNotes,lrg.Description ,CASE WHEN sp.ScheduledPersonID IS NULL THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '')
                    THEN (sd.Forename + ' ' + sd.Surname) ELSE (sd.PreferredForename + ' ' + sd.Surname)END
                  ELSE  (sp.DisplayFirstName + ' '+ sp.DisplayLastName) END  AS FullName
                FROM      Staff_Web_Config_LeaveGroups_Link swcl (NOLOCK)
                INNER JOIN        LeaveRequestGroups lrg (NOLOCK) ON swcl.LeaveGroupID = lrg.ID 
                INNER  JOIN		Users u (NOLOCK) on u.NetLogin = swcl.Login
                INNER JOIN		StaffDetails sd (NOLOCK) on sd.NetLogin = swcl.Login
                LEFT JOIN ScheduledPeople sp (NOLOCK) on sp.StaffDetailsID = sd.StaffID and sp.UserID=u.UserID
                WHERE             (swcl.Login = @netlogin) 
                  AND               (swcl.LeaveGroupID = @levegroupid) AND (swcl.IsActive=1)
END
