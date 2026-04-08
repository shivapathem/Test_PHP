USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_isFreelencerByNetlogin]    Script Date: 28/06/2024 17:54:08 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_get_isFreelencerByNetlogin]
@strLogin VARCHAR(50) 


AS
BEGIN
	
	SET NOCOUNT ON;

	SELECT DISTINCT sp.Displayname,
					sp.Staffdetailsid,
					sp.Userid,
					sp.Staffdetailsid as Staffid,
					spl.Teamid,
					spl.Enddate,
					us.Netlogin
	FROM Scheduledpeople sp 
	INNER JOIN users us on us.UserID = sp.UserID
	INNER JOIN Scheduledpersonteam_link spl ON sp.Scheduledpersonid = spl.Scheduledpersonid
    INNER JOIN schedulingTeams ST on st.schedulingTeamId = spl.TeamID
	WHERE Isnull(spl.Enddate, '9999-01-01') >= Getdate()
	  AND spl.Ishometeam = 1
	  AND spl.scheduledType = 1
	  AND st.schedulingTeamName = 'Freelancers'
	  AND us.Netlogin = @strlogin

END
