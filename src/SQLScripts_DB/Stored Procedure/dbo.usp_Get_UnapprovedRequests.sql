USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Get_UnapprovedRequests]    Script Date: 07/08/2025 20:10:13 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_Get_UnapprovedRequests]
	-- Add the parameters for the stored procedure here
	@strUser varchar(50),
	@strToday varchar(50)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SET NOCOUNT ON;

		SELECT lr.Description AS GroupDescription,
			   lr.ID          AS GroupID,
			   ud.UD_DisplayName  AS FullName,
			   rt.description AS TypeDescription,
			   rt.ID          AS TypeID,
			   rq.dDate,
			   rq.isOK,
			   rq.Unlikely,
			   rq.NotPossible,
			   ISNULL(rq.UserComments, '') AS UserComments,
			   ISNULL(rq.Comments, '')     AS Comments,
			   rq.Created,
			   rq.ID,
			   rq.Login
		 FROM  leaverequestgroups (nolock) LR
		 INNER JOIN staff_web_config_leavegroups_link (nolock) SW ON lr.id =  sw.leavegroupid
		 INNER JOIN requesttypes (nolock) RT ON lr.id = RT.groupid
		 INNER JOIN requests (nolock) RQ ON RT.id = RQ.requesttype
		 INNER JOIN UserDetails ud(nolock) on ud.UD_UserID=RQ.ScheduledPersonID
		 WHERE sw.login = @strUser 
		   AND sw.admin > 0 
		   AND RQ.ddate >= CONVERT(DATETIME, @strToday, 102) 
		   AND RQ.approved = 0
		   AND RQ.deleted = 0
		   AND sw.IsActive = 1
		ORDER  BY groupdescription,
				  RQ.ddate 


END

