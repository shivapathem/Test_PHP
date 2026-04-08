USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetch_request_by_id]    Script Date: 07/08/2025 20:09:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_fetch_request_by_id]
	-- Add the parameters for the stored procedure here
	@requestId int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SET NOCOUNT ON;
    SELECT	Requests.Login,
			Requests.Unlikely, 
			Requests.dDate,
			Requests.UserComments,
			Requests.Comments,
			Requests.Approved,
			Requests.ScheduledPersonID,
			Requests.RequestType,
			LeaveRequestGroups.ID as GroupID,
			Requests.NotPossible,
			ud.UD_DisplayName AS FullName,
			ud.UD_InternalEmail AS RequesterEmail, 
			RequestTypes.description + ' (' + LeaveRequestGroups.Description + ')' AS Description,
			LeaveRequestGroups.emailcopiesto AS EmailCC,
			LeaveRequestGroups.email AS EmailFrom
    FROM LeaveRequestGroups (NOLOCK)
    INNER JOIN RequestTypes (NOLOCK) ON LeaveRequestGroups.ID = RequestTypes.GroupID 
    INNER JOIN Requests (NOLOCK) ON RequestTypes.ID = Requests.RequestType
    INNER JOIN UserDetails ud(nolock) on ud.UD_UserID=Requests.ScheduledPersonID
    WHERE (Requests.ID = @requestId)
END