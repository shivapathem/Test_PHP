USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Get_UserAprrovedLeavsEmailToSend]    Script Date: 21/09/2025 23:23:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER        PROCEDURE [dbo].[usp_Get_UserAprrovedLeavsEmailToSend] 
	@netlogin varchar(100)
AS
BEGIN

	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
   SET NOCOUNT ON;

		SELECT ud.UD_NetLogin as Login,
			   UD_DisplayName AS FullName,
			   LRG.Description AS GroupDescription,
			   leave_types.description        AS TypeDescription,
			   LA.Approved,
			   LT.Description AS LeaveCategory,
			   RL.Amount AS LeaveCategoryAmount,
			   LA.ID,
			   LA.dDate,
			   LRG.ID          AS GroupID,
			   LRG.email       AS EmailFrom,
			   LRG.emailcopiesto,
			   LA.OfficeComments,
			   LA.Comments,
			   UD_InternalEmail     AS UserEmail,
			   LA.LeaveStartTime,
			   LA.LeaveEndTime,
			   LA.sentOptionValue
		FROM   LeaveApplications LA(NOLOCK)
		INNER JOIN leave_types (NOLOCK)  ON LA.LeaveTypesID = leave_types.ID
		INNER JOIN LeaveRequestGroups LRG (NOLOCK) ON leave_types.GroupID = LRG.ID
		INNER JOIN UserDetails UD ON UD.UD_UserID = LA.SchedulingPersonID --and la.Login=ud.UD_NetLogin
		 LEFT JOIN ref_LeaveApplications_Amounts RL ON RL.ApplicationID = LA.ID
		 LEFT JOIN LeaveAllocateTypes LT ON LT.id = RL.LeaveTypeID
		WHERE LA.Sent = 0 
		  AND LA.Deleted = 0
		  AND LA.Approved = 1 
		  AND ( UD_NetLogin = CASE WHEN @netlogin Is Null
								THEN UD_NetLogin 
								else @netlogin 
							end)
		  AND LA.sentOptionValue = 0
		  AND (LA.sentOptionValue = CASE WHEN @netlogin Is Null 
										 then 0 
										 else la.sentOptionValue 
									 end)
		ORDER BY LA.dDate ASC;
END