USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetch_requestCommentinfo_byRequestID]    Script Date: 06/08/2025 19:02:36 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE  [dbo].[usp_fetch_requestCommentinfo_byRequestID]
	-- Add the parameters for the stored procedure here
	@requestID int
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SET NOCOUNT ON;
	SELECT		Requests.dDate,
				Requests.Login,
				Requests.Comments,
				Requests.UserComments,
				Requests.Created,
				Requests.ID, 
				RequestTypes.description, 
				RequestTypes.SendEmails AS SentEmail,
				LeaveRequestGroups.Description AS GroupDescription, 
				sp.UD_DisplayName AS FullName,
				sp.UD_NetLogin AS Login,
				LeaveRequestGroups.email,
				LeaveRequestGroups.emailcopiesto
    FROM  Requests (Nolock)
    INNER JOIN RequestTypes (Nolock) ON Requests.RequestType = RequestTypes.ID 
    INNER JOIN LeaveRequestGroups (Nolock) ON RequestTypes.GroupID = LeaveRequestGroups.ID 
    INNER JOIN UserDetails as sp (Nolock) ON sp.UD_UserID = Requests.ScheduledPersonID 
    WHERE (Requests.ID = @requestID)
END