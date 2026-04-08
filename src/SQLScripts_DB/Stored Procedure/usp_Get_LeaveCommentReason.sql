USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_Get_LeaveCommentReason]    Script Date: 30/06/2022 17:23:13 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE OR ALTER    PROCEDURE [dbo].[usp_Get_LeaveCommentReason] 
	-- Add the parameters for the stored procedure here TT
	@leaveid int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

			SELECT distinct la.OfficeComments,Name AS ReasonName , la.Comments as UserComments
			 from LeaveApplications  la (nolock)
			INNER JOIN ref_LeaveApplications_Amounts RFL  (nolock) ON RFL.ApplicationID = la.ID
			INNER JOIN LeaveExceptionalTypes let  (nolock) on let.ID = RFL.ReasonID
			WHERE la.LeaveID = @leaveid  and la.Deleted = 0
				
END

