USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetRequestsForWeek]    Script Date: 26/04/2022 16:10:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_GetRequestsForWeek]
	-- Add the parameters for the stored procedure here
	@strStartDate varchar(50), 
	@strEndDate varchar(50)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	SET NOCOUNT ON;
	SELECT Requests.dDate,Requests.RequestType,Requests.Login,RequestTypes.AffectLocks, RequestTypes.description + '-' + LeaveRequestGroups.Description AS Description, Requests.isOK, Requests.Approved, Requests.Unlikely, Requests.UserComments,Requests.Created, Requests.Comments,Requests.ShortNotice,Requests.ID,RequestTypes.GroupID,
     Requests.ID as RequestID, Requests.ScheduledPersonID
    FROM Requests (Nolock) 
    INNER JOIN RequestTypes (Nolock) ON Requests.RequestType = RequestTypes.ID 
    INNER JOIN LeaveRequestGroups (Nolock) ON RequestTypes.GroupID = LeaveRequestGroups.ID
    WHERE         
	Requests.dDate >= convert(datetime,@strStartDate,102) 
    AND (Requests.dDate <= convert(datetime,@strEndDate,102)) 
    AND (Requests.Deleted = 0 AND Requests.Approved=1)
    ORDER BY  Requests.dDate

END
