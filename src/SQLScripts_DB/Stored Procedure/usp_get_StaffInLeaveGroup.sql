USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_StaffInLeaveGroup]    Script Date: 27/12/2025 17:13:53 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER            PROCEDURE [dbo].[usp_get_StaffInLeaveGroup] 
	@groupid int
AS
BEGIN

	SET NOCOUNT ON;

	SELECT ud.UD_DisplayLastName+', '+ud.UD_DisplayFirstName     AS userDisplayName,
		   CASE
			 WHEN st.schedulingteamid IS NULL THEN '-'
			 ELSE st.schedulingteamname
		   END                   AS teamname,
		   Isnull(swcl.admin, 0) AS Admin,
		   swcl.eft,
		   swcl.eft1,
		   swcl.eftsummer,
		   swcl.login,
		   CASE
			 WHEN Datalength(swcl.eftnotes) IS NULL THEN 0
			 ELSE Datalength(swcl.eftnotes)
		   END                   AS EFTNotes,
		   swcl.id
	FROM   staff_web_config_leavegroups_link swcl (nolock)
	INNER JOIN userdetails(nolock) ud
			ON ud_userid = swcl.scheduledpersonid
	 LEFT JOIN scheduledpersonteam_link spl (nolock)
			ON spl.scheduledpersonid = ud.ud_userid
			AND spl.ishometeam = 1
			AND CAST(Getdate() AS date) BETWEEN startdate AND enddate
			AND spl.scheduledtype = 1
	 LEFT JOIN schedulingteams st (nolock)
			ON st.schedulingteamid = spl.teamid
			AND st.isactive = 1
	WHERE swcl.leavegroupid = @groupid
	  AND swcl.isactive = 1
	ORDER BY userdisplayname 

END